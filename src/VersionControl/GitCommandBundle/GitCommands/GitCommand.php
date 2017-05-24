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

use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Stopwatch\Stopwatch;
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
     * @var TokenStorage
     */
    protected $securityContext;

    /**
     * The git gitEnvironment entity.
     *
     * @var GitEnvironmentInterface
     */
    protected $gitEnvironment;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    public $dispatcher;

    /**
     * Git Command Logger.
     *
     * @var \VersionControl\GitCommandBundle\Logger\GitCommandLogger
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
     * @var \VersionControl\GitCommandBundle\Service\SshProcessInterface
     */
    private $sshProcess;

    /**
     * Sftp Process.
     *
     * @var \VersionControl\GitCommandBundle\Service\SftpProcessInterface
     */
    private $sftpProcess;

    /**
     * Cache in memory.
     *
     * @var \Doctrine\Common\Cache\CacheProvider
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
     * @param string $command      command to run
     * @param bool   $cacheCommand command to run
     * @param bool   $trim         do not trim response. Maybe need for some command responses
     *
     * @return string Result of command
     *
     * @throws \RuntimeException
     */
    public function runCommand($command, $cacheCommand = true, $trim = true)
    {
        $response = '';
        if ($this->stopwatch) {
            $this->stopwatch->start('git_request', 'version_control');
        }

        $fullCommand = sprintf('cd %s && %s', $this->gitPath, $command);
        $cacheId = md5($this->gitEnvironment->getId().$fullCommand);

        if ($this->gitEnvironment->getSsh() === true) {
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
        } else {
            //Run local commands
            $start = microtime(true);
            $response = $this->runLocalCommand($command);

            $this->logCommand($fullCommand, 'local', array(), $start);
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
    private function runRemoteCommand($fullCommand)
    {
        $start = microtime(true);

        $this->sshProcess->run(array($fullCommand), $this->gitEnvironment->getHost(), $this->gitEnvironment->getUsername(), 22, $this->gitEnvironment->getPassword(), null, $this->gitEnvironment->getPrivateKey(), $this->gitEnvironment->getPrivateKeyPassword());
        $this->logCommand($fullCommand, 'remote', array('host' => $this->gitEnvironment->getHost()), $start, $this->sshProcess->getStdout(), $this->sshProcess->getStderr(), $this->sshProcess->getExitStatus());

        return $this->sshProcess->getStdout();
    }

    /**
     * Run local command.
     *
     * @param string $command
     *
     * @return string Commands response
     */
    private function runLocalCommand($command)
    {
        $fullCommand = sprintf('cd %s && %s', $this->gitPath, $command);

        //Run local commands
        if (is_array($command)) {
            //$finalCommands = array_merge(array('cd',$this->gitPath,'&&'),$command);
            $builder = new ProcessBuilder($command);
            $builder->setPrefix('cd '.$this->gitPath.' && ');
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
        } else {
            return $this->exitCode;
        }
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
     * @return \VersionControl\GitCommandBundle\GitCommands
     */
    protected function setGitPath($gitPath)
    {
        $this->gitPath = rtrim(trim($gitPath), DIRECTORY_SEPARATOR );

        return $this;
    }

    /**
     * Sets the Git Environment.
     *
     * @param GitEnvironmentInterface $gitEnvironment
     *
     * @return \VersionControl\GitCommandBundle\GitCommands\GitCommand
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
     * @return \VersionControl\GitCommandBundle\GitCommands\GitCommand
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
     * @return type
     */
    public function getGitEnvironment()
    {
        return $this->gitEnvironment;
    }

    public function getSecurityContext()
    {
        return $this->securityContext;
    }

    public function setSecurityContext(TokenStorage $securityContext)
    {
        $this->securityContext = $securityContext;
    }

    public function getDispatcher()
    {
        return $this->dispatcher;
    }

    public function setDispatcher(\Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher)
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
     * @param array  $data
     * @param int    $start
     */
    public function logCommand($command, $method, $data, $start, $response = '', $error = '', $exitStatus = 0)
    {
        if (!$this->logger or !$this->logger instanceof GitCommandLogger) {
            return;
        }
        $time = microtime(true) - $start;

        $this->logger->logCommand($command, $method, $data, $time, $response, $error, $exitStatus);
    }

    /**
     * Sets the SSH Process.
     *
     * @param SshProcess $sshProcess
     *
     * @return \VersionControl\GitCommandBundle\GitCommands\GitCommand
     */
    public function setSshProcess(SshProcessInterface $sshProcess)
    {
        $this->sshProcess = $sshProcess;

        return $this;
    }

    /**
     * Sets the SFTP Process.
     *
     * @param SshProcess $sftpProcess
     *
     * @return \VersionControl\GitCommandBundle\GitCommands\GitCommand
     */
    public function setSftpProcess(SftpProcessInterface $sftpProcess)
    {
        $this->sftpProcess = $sftpProcess;

        return $this;
    }

    /**
     * Gets the SFTP Process.
     *
     * @return \VersionControl\GitCommandBundle\Service\SftpProcessInterface
     */
    public function getSftpProcess()
    {
        $this->sftpProcess->setGitEnviroment($this->gitEnvironment);

        return $this->sftpProcess;
    }

    public function setCache(\Doctrine\Common\Cache\CacheProvider $cache)
    {
        $this->cache = $cache;
    }

    /**
     * Get git command groups.
     *
     * @param string $name
     *
     * @return \VersionControl\GitCommandBundle\GitCommands\GitCommand
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
}
