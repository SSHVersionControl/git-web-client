<?php

namespace VersionContol\GitControlBundle\Utility\GitCommands;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;

use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use VersionContol\GitControlBundle\Entity\Project;
use VersionContol\GitControlBundle\Utility\SshProcessInterface;
use VersionContol\GitControlBundle\Utility\ProjectEnvironmentStorage;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use VersionContol\GitControlBundle\Event\GitAlterFilesEvent;
use VersionContol\GitControlBundle\Entity\ProjectEnvironment;
use VersionContol\GitControlBundle\Logger\GitCommandLogger;
use Symfony\Component\Stopwatch\Stopwatch;

use VersionContol\GitControlBundle\Utility\GitCommands\Command as Command;
use VersionContol\GitControlBundle\Utility\GitCommands\Exception\InvalidArgumentException;

class GitCommand {
    
    protected $gitPath;
    
    /**
     * @var TokenStorage
     */
    protected $securityContext;
    
    /**
     * The git projectEnvironment entity
     * @var Project
     */
    protected $projectEnvironment;
    

    /**
     * @var ProjectEnvironmentStorage
     */
    protected $projectEnvironmentStorage;
    
    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
     */
    public $dispatcher;
    
    /**
     * Git Command Logger
     * @var \VersionContol\GitControlBundle\Logger\GitCommandLogger 
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
     * @var \VersionContol\GitControlBundle\Utility\SshProcessInterface 
     */
    private $sshProcess;
    
    
    /**
     * Wrapper function to run shell commands. Supports local and remote commands
     * depending on the projectEnvironment details
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
        
        if($this->projectEnvironment->getSsh() === true){
            $fullCommand = sprintf('cd %s && %s',$this->gitPath,$command);
            //$sshProcess = new SshProcess();
            $this->sshProcess->run(array($fullCommand),$this->projectEnvironment->getHost(),$this->projectEnvironment->getUsername(),22,$this->projectEnvironment->getPassword());
            $this->logCommand($fullCommand,'remote',array('host'=>$this->projectEnvironment->getHost()),$start);
            
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
     * @return \VersionContol\GitControlBundle\Utility\GitCommands
     */
    protected function setGitPath($gitPath) {
        $this->gitPath = rtrim(trim($gitPath),'/');
        return $this;
    }

    /**
     * Sets the project entity
     * @param Project $project
     */
    public function setProject(Project $project) {
        
        $this->projectEnvironment = $this->projectEnvironmentStorage->getProjectEnviromment($project);
        $this->setGitPath($this->projectEnvironment->getPath());
        return $this;
    }
   
    /**
     * Allows you to override the project Environment
     * @param ProjectEnvironment $projectEnvironment
     * @return \VersionContol\GitControlBundle\Utility\GitCommands\GitCommand
     */
    public function overRideProjectEnvironment(ProjectEnvironment $projectEnvironment){
        $this->projectEnvironment = $projectEnvironment;
        $this->setGitPath($this->projectEnvironment->getPath());
        return $this;
    }
    
    /**
     * Gets Project Environment
     * @return type
     */
    public function getProjectEnvironment() {
        return $this->projectEnvironment;
    }
    
    public function getSecurityContext() {
        return $this->securityContext;
    }

    public function getProjectEnvironmentStorage() {
        return $this->projectEnvironmentStorage;
    }

    public function setSecurityContext(TokenStorage $securityContext) {
        $this->securityContext = $securityContext;

    }

    public function setProjectEnvironmentStorage(ProjectEnvironmentStorage $projectEnvironmentStorage) {
        $this->projectEnvironmentStorage = $projectEnvironmentStorage;
    }

    public function getDispatcher() {
        return $this->dispatcher;
    }

    public function setDispatcher(\Symfony\Component\EventDispatcher\EventDispatcherInterface $dispatcher) {
        $this->dispatcher = $dispatcher;
        return $this;
    }

    public function triggerGitAlterFilesEvent($eventName = 'git.alter_files'){
        $event = new GitAlterFilesEvent($this->projectEnvironment,array());
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
     * @return \VersionContol\GitControlBundle\Utility\GitCommands\GitCommand
     */
    public function setSshProcess(\VersionContol\GitControlBundle\Utility\SshProcessInterface $sshProcess) {
        $this->sshProcess = $sshProcess;
        return $this;
    }

    /**
     * Get git command groups
     * @param string $name
     * @return \VersionContol\GitControlBundle\Utility\GitCommands\GitCommand
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