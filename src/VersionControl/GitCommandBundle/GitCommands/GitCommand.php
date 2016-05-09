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
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Stopwatch\Stopwatch;

use VersionControl\GitCommandBundle\Service\SshProcessInterface;
use VersionControl\GitCommandBundle\Service\SftpProcessInterface;
use VersionControl\GitCommandBundle\GitCommands\Command as Command;
use VersionControl\GitCommandBundle\GitCommands\Exception\InvalidArgumentException;
use VersionControl\GitCommandBundle\Logger\GitCommandLogger;
use VersionControl\GitCommandBundle\Event\GitAlterFilesEvent;


use VersionControl\GitCommandBundle\GitCommands\GitEnvironmentInterface;


/**
 * @author Paul Schweppe <paulschweppe@gmail.com>
 */
class GitCommand {
    
    protected $gitPath;
    
    /**
     * @var TokenStorage
     */
    protected $securityContext;
    
    /**
     * The git gitEnvironment entity
     * @var GitEnvironmentInterface
     */
    protected $gitEnvironment;
    
    
    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    public $dispatcher;
    
    /**
     * Git Command Logger
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
     * SSH Process
     * @var \VersionControl\GitCommandBundle\Service\SshProcessInterface 
     */
    private $sshProcess;
    
     /**
     * Sftp Process
     * @var \VersionControl\GitCommandBundle\Service\SftpProcessInterface 
     */
    private $sftpProcess;
    
    
    /**
     * Wrapper function to run shell commands. Supports local and remote commands
     * depending on the gitEnvironment details
     * 
     * @param string $command command to run
     * @return string Result of command
     * @throws \RuntimeException
     */
    public function runCommand($command){
        
        if ($this->stopwatch) {
            $this->stopwatch->start('git_request', 'version_control');
        }
        $start = microtime(true);
        
        if($this->gitEnvironment->getSsh() === true){
            $fullCommand = sprintf('cd %s && %s',$this->gitPath,$command);
            //$sshProcess = new SshProcess();
            $this->sshProcess->run(array($fullCommand),$this->gitEnvironment->getHost(),$this->gitEnvironment->getUsername()
                    ,22,$this->gitEnvironment->getPassword(),null
                    ,$this->gitEnvironment->getPrivateKey(),$this->gitEnvironment->getPrivateKeyPassword());
            $this->logCommand($fullCommand,'remote',array('host'=>$this->gitEnvironment->getHost()),$start);
            
            return $this->sshProcess->getStdout();
        }else{
            if(is_array($command)){
                //$finalCommands = array_merge(array('cd',$this->gitPath,'&&'),$command);
                $builder = new ProcessBuilder($command);
                $builder->setPrefix('cd '.$this->gitPath.' && ');
                $process = $builder->getProcess();
                
            }else{
                $fullCommand = sprintf('cd %s && %s',$this->gitPath,$command);
                $process = new Process($fullCommand);
            }
            //return exec($fullCommand);
            //print_r($process->getCommandLine());
            $process->run();

            $this->logCommand($fullCommand,'local',array(),$start);
            
            // executes after the command finishes
            if (!$process->isSuccessful()) {
                if(trim($process->getErrorOutput()) !== ''){
                    throw new \RuntimeException($process->getErrorOutput());
                }else{
                    //Git returns a false with a reponse. So return as if successfull
                    return $process->getOutput();
                }
            }
            
            return $process->getOutput();
        }
    }   
    
    /**
     * Gets the git path
     * @return type
     */
    public function getGitPath() {
        return $this->gitPath;
    }
    
    /**
     * Sets the git path. 
     * @param string $gitPath
     * @return \VersionControl\GitCommandBundle\GitCommands
     */
    protected function setGitPath($gitPath) {
        $this->gitPath = rtrim(trim($gitPath),'/');
        return $this;
    }

    
    /**
     * Sets the Git Environment
     * @param GitEnvironmentInterface $gitEnvironment
     * @return \VersionControl\GitCommandBundle\GitCommands\GitCommand
     */
    public function setGitEnvironment(GitEnvironmentInterface $gitEnvironment){
        $this->gitEnvironment = $gitEnvironment;
        $this->setGitPath($this->gitEnvironment->getPath());
        return $this;
    }
   
    /**
     * Allows you to override the git Environment
     * @param GitEnvironmentInterface $gitEnvironment
     * @return \VersionControl\GitCommandBundle\GitCommands\GitCommand
     */
    public function overRideGitEnvironment(GitEnvironmentInterface $gitEnvironment){
        $this->gitEnvironment = $gitEnvironment;
        $this->setGitPath($this->gitEnvironment->getPath());
        return $this;
    }
    
    /**
     * Gets git Environment
     * @return type
     */
    public function getGitEnvironment() {
        return $this->gitEnvironment;
    }
    
    public function getSecurityContext() {
        return $this->securityContext;
    }

    public function setSecurityContext(TokenStorage $securityContext) {
        $this->securityContext = $securityContext;

    }

    public function getDispatcher() {
        return $this->dispatcher;
    }

    public function setDispatcher(\Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher) {
        $this->dispatcher = $dispatcher;
        return $this;
    }

    public function triggerGitAlterFilesEvent($eventName = 'git.alter_files'){
        $event = new GitAlterFilesEvent($this->gitEnvironment,array());
        $this->dispatcher->dispatch($eventName, $event);
    }

    public function getLogger() {
        return $this->logger;
    }

    public function setLogger(GitCommandLogger $logger) {
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
    public function logCommand($command, $method, $data, $start)
    {
        if (!$this->logger or !$this->logger instanceof GitCommandLogger) {
            return;
        }
        $time = microtime(true) - $start;
        
        $this->logger->logCommand($command, $method, $data, $time);
    }
    
    /**
     * Sets the SSH Process
     * @param SshProcess $sshProcess
     * @return \VersionControl\GitCommandBundle\GitCommands\GitCommand
     */
    public function setSshProcess(SshProcessInterface $sshProcess) {
        $this->sshProcess = $sshProcess;
        return $this;
    }
    
    /**
     * Sets the SFTP Process
     * @param SshProcess $sftpProcess
     * @return \VersionControl\GitCommandBundle\GitCommands\GitCommand
     */
    public function setSftpProcess(SftpProcessInterface $sftpProcess) {
        $this->sftpProcess = $sftpProcess;
        return $this;
    }
    
    /**
     * Gets the SFTP Process
     * 
     * @return \VersionControl\GitCommandBundle\Service\SftpProcessInterface
     */
    public function getSftpProcess() {
        $this->sftpProcess->setGitEnviroment($this->gitEnvironment);
        return $this->sftpProcess;
    }

    /**
     * Get git command groups
     * @param string $name
     * @return \VersionControl\GitCommandBundle\GitCommands\GitCommand
     * @throws InvalidArgumentException
     */
    public function command($name){
        switch (trim($name)) {
            case 'branch':
                $command = new Command\GitBranchCommand($this);
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