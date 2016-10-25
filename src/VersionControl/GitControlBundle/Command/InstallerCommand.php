<?php
/*
 * This file is part of the GitControlBundle package.
 *
 * (c) Paul Schweppe <paulschweppe@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace VersionControl\GitControlBundle\Command;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\ConnectionException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\CacheClearer\CacheClearerInterface;
use VersionControl\GitControlBundle\Installer\DatabaseInstall;

/**
 * Command line based installer, Use to install and set up the application.
 *
 * @author Paul Schweppe <paulschweppe@gmail.com>
 */
class InstallerCommand extends Command
{
    /** @var \Doctrine\DBAL\Connection */
    private $db;

    /** @var \VersionControl\GitControlBundle\Installer\DatabaseInstall */
    private $installer;

    /** @var \Symfony\Component\Console\Output\OutputInterface */
    private $output;

    /** @var \Symfony\Component\HttpKernel\CacheClearer\CacheClearerInterface */
    private $cacheClearer;

    /** @var \Symfony\Component\Filesystem\Filesystem */
    private $filesystem;

    /** @var string */
    private $cacheDir;

    /** @var string */
    private $environment;

    /**
     * FOS User Manager.
     *
     * @var type
     */
    private $userManager;

    const EXIT_DATABASE_NOT_FOUND_ERROR = 3;
    const EXIT_GENERAL_DATABASE_ERROR = 4;
    const EXIT_PARAMETERS_NOT_FOUND = 5;
    const EXIT_MISSING_PERMISSIONS = 7;

    public function __construct(
        Connection $db,
        DatabaseInstall $installer,
        CacheClearerInterface $cacheClearer,
        Filesystem $filesystem,
        $cacheDir,
        $environment,
        $userManager
    ) {
        $this->db = $db;
        $this->installer = $installer;
        $this->cacheClearer = $cacheClearer;
        $this->filesystem = $filesystem;
        $this->cacheDir = $cacheDir;
        $this->environment = $environment;
        $this->userManager = $userManager;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('version:install');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $this->installer->setOutput($output);

        $this->checkPermissions();
        $this->checkParameters();
        $this->checkDatabase();

        $this->installer->importSchema();

        if ($this->environment == 'test') {
            $this->installer->importTestData();
        } else {
            $this->installer->importData();
        }

        $this->cacheClear($output);
    }

    private function checkPermissions()
    {
        if (!is_writable('app/config')) {
            $this->output->writeln('app/config is not writable');
            exit(self::EXIT_MISSING_PERMISSIONS);
        }
    }

    private function checkParameters()
    {
        $parametersFile = 'app/config/parameters.yml';
        if (!is_file($parametersFile)) {
            $this->output->writeln("Required configuration file $parametersFile not found");
            exit(self::EXIT_PARAMETERS_NOT_FOUND);
        }
    }

    private function checkDatabase()
    {
        try {
            if (!$this->configuredDatabaseExists()) {
                $this->output->writeln(
                    sprintf(
                        "The configured database '%s' does not exist. Please run 'php app/console doctrine:database:create' to create the database first",
                        $this->db->getDatabase()
                    )
                );

                exit(self::EXIT_DATABASE_NOT_FOUND_ERROR);
            }
        } catch (ConnectionException $e) {
            $this->output->writeln('An error occured connecting to the database:');
            $this->output->writeln($e->getMessage());
            $this->output->writeln('Please check the database configuration in parameters.yml');
            exit(self::EXIT_GENERAL_DATABASE_ERROR);
        }
    }

    /**
     * @throws \Exception if an unexpected database error occurs
     */
    private function configuredDatabaseExists()
    {
        try {
            $this->db->connect();
        } catch (ConnectionException $e) {
            // @todo 1049 is MySQL's code for "database doesn't exist", refactor
            if ($e->getPrevious()->getCode() == 1049) {
                return false;
            }
            throw $e;
        }

        return true;
    }

    private function cacheClear(OutputInterface $output)
    {
        if (!is_writable($this->cacheDir)) {
            throw new \RuntimeException(sprintf('Unable to write in the "%s" directory', $this->cacheDir));
        }

        $output->writeln(sprintf('Clearing cache for directory <info>%s</info>', $this->cacheDir));
        $oldCacheDir = $this->cacheDir.'_old';

        if ($this->filesystem->exists($oldCacheDir)) {
            $this->filesystem->remove($oldCacheDir);
        }

        $this->cacheClearer->clear($this->cacheDir);

        $this->filesystem->rename($this->cacheDir, $oldCacheDir);
        $this->filesystem->remove($oldCacheDir);
    }

    /**
     * Creates a new admin user.
     *
     * @param string $username
     * @param string $password
     * @param string $email
     * @param string $name
     *
     * @return \FOS\UserBundle\Model\UserInterface
     */
    protected function createUser($username, $password, $email, $name)
    {
        $user = $this->userManager->createUser();
        $user->setUsername($username);
        $user->setEmail($email);
        $user->setPlainPassword($password);
        $user->setName($name);

        $user->setEnabled((bool) true);
        $user->addRole('ROLE_ADMIN');
        //$user->setSuperAdmin((Boolean) true);

        $this->userManager->updateUser($user);

        return $user;
    }
}
