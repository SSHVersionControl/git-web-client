<?php
/*
 * This file is part of the GitCommandBundle package.
 *
 * (c) Paul Schweppe <paulschweppe@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace VersionControl\GitCommandBundle\GitCommands;

use Doctrine\Common\Cache\CacheProvider;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Process\Exception\LogicException;
use Symfony\Component\Process\Exception\RuntimeException;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Stopwatch\Stopwatch;
use VersionControl\GitCommandBundle\GitCommands\Command\GitUserInterface;
use VersionControl\GitCommandBundle\Service\SshProcessInterface;
use VersionControl\GitCommandBundle\Service\SftpProcessInterface;
use VersionControl\GitCommandBundle\GitCommands\Exception\InvalidArgumentException;
use VersionControl\GitCommandBundle\Logger\GitCommandLogger;
use VersionControl\GitCommandBundle\Event\GitAlterFilesEvent;
use VersionControl\GitCommandBundle\GitCommands\Exception\RunGitCommandException;

/**
 * @author Paul Schweppe <paulschweppe@gmail.com>
 */
class GitCommand
{
    protected $gitPath;

    /**
     * @var GitUserInterface
     */
    private $gitUser;

    /**
     * The git gitEnvironment entity.
     *
     * @var GitEnvironmentInterface
     */
    protected $gitEnvironment;

    /**
     * @var EventDispatcherInterface
     */
    public $dispatcher;

    /**
     * Git Command Logger.
     *
     * @var GitCommandLogger
     */
    protected $logger;

    /**
     * Symfony's debugging Stopwatch.
     *
     * @var Stopwatch|null
     */
    private $stopwatch;

    /**
     * SSH Process.
     *
     * @var SshProcessInterface
     */
    private $sshProcess;

    /**
     * Sftp Process.
     *
     * @var SftpProcessInterface
     */
    private $sftpProcess;

    /**
     * Cache in memory.
     *
     * @var CacheProvider
     */
    private $cache;

    /**
     * Last Exit code of local command.
     *
     * @var int
     */
    private $exitCode;

    /**
     * Wrapper function to run shell commands. Supports local and remote commands
     * depending on the gitEnvironment details.
     *
     * @param string $command command to run
     * @param bool $cacheCommand command to run
     * @param bool $trim do not trim response. Maybe need for some command responses
     *
     * @return string Result of command
     *
     * @throws RuntimeException
     * @throws LogicException
     * @throws \RuntimeException
     * @throws RunGitCommandException
     */
    public function runCommand($command, $cacheCommand = true, $trim = true): string
    {
        if ($this->stopwatch) {
            $this->stopwatch->start('git_request', 'version_control');
        }

        $fullCommand = sprintf('cd %s && %s', $this->gitPath, $command);
        $cacheId = md5($this->gitEnvironment->getId() . $fullCommand);

        if ($this->gitEnvironment->getSsh() === false) {
            //Run local commands
            $start = microtime(true);
            $response = $this->runLocalCommand($command);

            $this->logCommand($fullCommand, 'local', array(), $start);

            return $trim === true ? trim($response) : $response;
        }

        //Run remote command over ssh
        if ($cacheCommand === true) {
            $response = $this->cache->fetch($cacheId);
            if ($response === false) {
                $response = $this->runRemoteCommand($fullCommand);
                $this->cache->save($cacheId, $response);
            }
        } else {
            $response = $this->runRemoteCommand($fullCommand);
        }

        return $trim === true ? trim($response) : $response;
    }

    /**
     * Run remote command over ssh.
     *
     * @param string $fullCommand
     *
     * @return string Commands response
     */
    private function runRemoteCommand($fullCommand): string
    {
        $start = microtime(true);

        $this->sshProcess->run(
            array($fullCommand),
            $this->gitEnvironment->getHost(),
            $this->gitEnvironment->getUsername(),
            22,
            $this->gitEnvironment->getPassword(),
            null,
            $this->gitEnvironment->getPrivateKey(),
            $this->gitEnvironment->getPrivateKeyPassword()
        );

        $this->logCommand(
            $fullCommand,
            'remote',
            array('host' => $this->gitEnvironment->getHost()),
            $start,
            $this->sshProcess->getStdout(),
            $this->sshProcess->getStderr(),
            $this->sshProcess->getExitStatus()
        );

        return $this->sshProcess->getStdout();
    }

    /**
     * Run local command.
     *
     * @param string|array $command
     *
     * @return string Commands response
     * @throws RuntimeException
     * @throws RunGitCommandException
     * @throws LogicException
     * @throws RunGitCommandException
     */
    private function runLocalCommand($command)
    {
        $fullCommand = sprintf('cd %s && %s', $this->gitPath, $command);

        //Run local commands
        if (is_array($command)) {
            //$finalCommands = array_merge(array('cd',$this->gitPath,'&&'),$command);
            $builder = new ProcessBuilder($command);
            $builder->setPrefix('cd ' . $this->gitPath . ' && ');
            $process = $builder->getProcess();
        } else {
            $process = new Process($fullCommand);
        }

        //Run Proccess
        $process->run();

        $this->exitCode = $process->getExitCode();

        $response = '';
        // executes after the command finishes
        if ($process->isSuccessful()) {
            $response = $process->getOutput();
            if (trim($process->getErrorOutput()) !== '') {
                $response = $process->getErrorOutput();
            }
        } else {
            if (trim($process->getErrorOutput()) !== '') {
                throw new RunGitCommandException($process->getErrorOutput());
            }
        }

        return $response;
    }

    public function getLastExitStatus()
    {
        if ($this->gitEnvironment->getSsh() === true) {
            return $this->sshProcess->getExitStatus();
        }

        return $this->exitCode;
    }

    /**
     * Gets the git path.
     *
     * @return type
     */
    public function getGitPath()
    {
        return $this->gitPath;
    }

    /**
     * Sets the git path.
     *
     * @param string $gitPath
     *
     * @return GitCommand
     */
    protected function setGitPath($gitPath)
    {
        $this->gitPath = rtrim(trim($gitPath), DIRECTORY_SEPARATOR);

        return $this;
    }

    /**
     * Sets the Git Environment.
     *
     * @param GitEnvironmentInterface $gitEnvironment
     *
     * @return GitCommand
     */
    public function setGitEnvironment(GitEnvironmentInterface $gitEnvironment)
    {
        $this->gitEnvironment = $gitEnvironment;
        $this->setGitPath($this->gitEnvironment->getPath());

        return $this;
    }

    /**
     * Allows you to override the git Environment.
     *
     * @param GitEnvironmentInterface $gitEnvironment
     *
     * @return GitCommand
     */
    public function overRideGitEnvironment(GitEnvironmentInterface $gitEnvironment)
    {
        $this->gitEnvironment = $gitEnvironment;
        $this->setGitPath($this->gitEnvironment->getPath());

        return $this;
    }

    /**
     * Gets git Environment.
     *
     * @return GitEnvironmentInterface
     */
    public function getGitEnvironment()
    {
        return $this->gitEnvironment;
    }

    public function getDispatcher()
    {
        return $this->dispatcher;
    }

    public function setDispatcher(EventDispatcherInterface $dispatcher)
    {
        $this->dispatcher = $dispatcher;

        return $this;
    }

    public function triggerGitAlterFilesEvent($eventName = 'git.alter_files')
    {
        $event = new GitAlterFilesEvent($this->gitEnvironment, array());
        $this->dispatcher->dispatch($eventName, $event);
    }

    public function getLogger()
    {
        return $this->logger;
    }

    public function setLogger(GitCommandLogger $logger)
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * Sets a stopwatch instance for debugging purposes.
     *
     * @param Stopwatch $stopwatch
     */
    public function setStopwatch(Stopwatch $stopwatch = null)
    {
        $this->stopwatch = $stopwatch;
    }

    /**
     * Log the query if we have an instance of ElasticaLogger.
     *
     * @param string $command
     * @param string $method
     * @param array $data
     * @param int $start
     * @param string $response
     * @param string $error
     * @param int $exitStatus
     */
    public function logCommand(
        $command,
        $method,
        $data,
        $start,
        $response = '',
        $error = '',
        $exitStatus = 0
    ) {
        if (!$this->logger || !$this->logger instanceof GitCommandLogger) {
            return;
        }
        $time = microtime(true) - $start;

        $this->logger->logCommand($command, $method, $data, $time, $response, $error, $exitStatus);
    }

    /**
     * Sets the SSH Process.
     *
     * @param SshProcessInterface $sshProcess
     *
     * @return GitCommand
     */
    public function setSshProcess(SshProcessInterface $sshProcess)
    {
        $this->sshProcess = $sshProcess;

        return $this;
    }

    /**
     * Sets the SFTP Process.
     *
     * @param SftpProcessInterface $sftpProcess
     *
     * @return GitCommand
     */
    public function setSftpProcess(SftpProcessInterface $sftpProcess)
    {
        $this->sftpProcess = $sftpProcess;

        return $this;
    }

    /**
     * Gets the SFTP Process.
     *
     * @return SftpProcessInterface
     */
    public function getSftpProcess()
    {
        $this->sftpProcess->setGitEnviroment($this->gitEnvironment);

        return $this->sftpProcess;
    }

    public function setCache(CacheProvider $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Get git command groups.
     *
     * @param string $name
     *
     * @return Command\GitBranchCommand|Command\GitCommitCommand|Command\GitDiffCommand|Command\GitFilesCommand|Command\GitInitCommand|Command\GitLogCommand|Command\GitTagCommand
     *
     * @throws InvalidArgumentException
     */
    public function command($name)
    {
        switch (trim($name)) {
            case 'branch':
                $command = new Command\GitBranchCommand($this);
                break;
            case 'tag':
                $command = new Command\GitTagCommand($this);
                break;
            case 'commit':
                $command = new Command\GitCommitCommand($this);
                break;
            case 'diff':
                $command = new Command\GitDiffCommand($this);
                break;
            case 'files':
                $command = new Command\GitFilesCommand($this);
                break;
            case 'init':
                $command = new Command\GitInitCommand($this);
                break;
            case 'log':
                $command = new Command\GitLogCommand($this);
                break;
            case 'status':
                $command = new Command\GitStatusCommand($this);
                break;
            case 'sync':
                $command = new Command\GitSyncCommand($this);
                break;
            case 'undo':
                $command = new Command\GitUndoCommand($this);
                break;
            default:
                throw new InvalidArgumentException(sprintf('Unknown command instance called: "%s"', $name));
        }

        return $command;
    }

    /**
     * @return GitUserInterface
     */
    public function getGitUser(): GitUserInterface
    {
        return $this->gitUser;
    }

    /**
     * @param GitUserInterface $gitUser
     */
    public function setGitUser(GitUserInterface $gitUser): void
    {
        $this->gitUser = $gitUser;
    }
}
