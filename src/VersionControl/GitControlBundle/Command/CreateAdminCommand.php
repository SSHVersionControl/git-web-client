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

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

/**
 * Command to create a new admin user for the version control application.
 *
 * @author Paul Schweppe<paulschweppe@gmail.com>
 */
class CreateAdminCommand extends ContainerAwareCommand
{
    /**
     * @see Command
     */
    protected function configure()
    {
        $this
            ->setName('version:admin:create')
            ->setDescription('Create a new admin user.')
            ->setDefinition(array(
                new InputArgument('username', InputArgument::REQUIRED, 'The username'),
                new InputArgument('email', InputArgument::REQUIRED, 'The email'),
                new InputArgument('password', InputArgument::REQUIRED, 'The password'),
                new InputArgument('name', InputArgument::REQUIRED, 'The display name'),
            ))
            ->setHelp(<<<'EOT'
The <info>version:admin:create</info> command creates an administrator user:

  <info>php app/console version:admin:create adminname</info>

This interactive shell will ask you for an email, display name and then a password.


EOT
            );
    }

    /**
     * @see Command
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $username = $input->getArgument('username');
        $email = $input->getArgument('email');
        $password = $input->getArgument('password');
        $name = $input->getArgument('name');

        $this->createuser($username, $password, $email, $name);

        $output->writeln(sprintf('Created user <comment>%s</comment>', $username));
    }

    /**
     * @see Command
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        if (!$this->getHelperSet()->has('question')) {
            $this->legacyInteract($input, $output);

            return;
        }

        $questions = array();

        if (!$input->getArgument('username')) {
            $question = new Question('Please choose a username:');
            $question->setValidator(function ($username) {
                if (empty($username)) {
                    throw new \Exception('Username can not be empty');
                }

                return $username;
            });
            $questions['username'] = $question;
        }

        if (!$input->getArgument('email')) {
            $question = new Question('Please choose an email:');
            $question->setValidator(function ($email) {
                if (empty($email)) {
                    throw new \Exception('Email can not be empty');
                }

                return $email;
            });
            $questions['email'] = $question;
        }

        if (!$input->getArgument('password')) {
            $question = new Question('Please choose a password:');
            $question->setValidator(function ($password) {
                if (empty($password)) {
                    throw new \Exception('Password can not be empty');
                }

                return $password;
            });
            $question->setHidden(true);
            $questions['password'] = $question;
        }

        if (!$input->getArgument('name')) {
            $question = new Question('Please choose a display name:');
            $question->setValidator(function ($name) {
                if (empty($name)) {
                    throw new \Exception('Display name can not be empty');
                }

                return $name;
            });
            $questions['name'] = $question;
        }

        foreach ($questions as $name => $question) {
            $answer = $this->getHelper('question')->ask($input, $output, $question);
            $input->setArgument($name, $answer);
        }
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
        $userManager = $this->getContainer()->get('fos_user.user_manager');

        $user = $userManager->createUser();
        $user->setUsername($username);
        $user->setEmail($email);
        $user->setPlainPassword($password);
        $user->setName($name);

        $user->setEnabled((bool) true);
        $user->addRole('ROLE_ADMIN');
        //$user->setSuperAdmin((Boolean) true);

        $userManager->updateUser($user);

        return $user;
    }
}
