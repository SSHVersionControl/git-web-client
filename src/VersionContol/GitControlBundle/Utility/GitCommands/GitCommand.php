<?php

namespace VersionContol\GitControlBundle\Utility\GitCommands;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;

use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use VersionContol\GitControlBundle\Entity\Project;
use VersionContol\GitControlBundle\Utility\SshProcess;
use VersionContol\GitControlBundle\Utility\ProjectEnvironmentStorage;

abstract class GitCommand {
    
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
     *
     * @var type Git Status Hash.
     * Used to make sure no changes has occurred since last check 
     * @var string hash
     */
    protected $statusHash;
    
    /**
     * @var ProjectEnvironmentStorage
     */
    protected $projectEnvironmentStorage;
    
    /**
     * Wrapper function to run shell commands. Supports local and remote commands
     * depending on the projectEnvironment details
     * 
     * @param string $command command to run
     * @return string Result of command
     * @throws \RuntimeException
     */
    protected function runCommand($command){
        
        if($this->projectEnvironment->getSsh() === true){
            $fullCommand = sprintf('cd %s && %s',$this->gitPath,$command);
            $sshProcess = new SshProcess();
            $sshProcess->run(array($fullCommand),$this->projectEnvironment->getHost(),$this->projectEnvironment->getUsername(),22,$this->projectEnvironment->getPassword());
            return $sshProcess->getStdout();
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

            // executes after the command finishes
            if (!$process->isSuccessful()) {
                
                throw new \RuntimeException($process->getErrorOutput());
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
     * Splits a block of text on newlines and returns an array
     *  
     * @param string $text Text to split
     * @param boolean $trimSpaces If true then each line is trimmed of white spaces. Default true. 
     * @return array Array of lines
     */
    protected function splitOnNewLine($text,$trimSpaces = true){
        if(!trim($text)){
            return array();
        }
        $lines = preg_split('/$\R?^/m', $text);
        if($trimSpaces){
            return array_map(array($this,'trimSpaces'),$lines); 
        }else{
            return $lines; 
        }
    }
    
    public function trimSpaces($value){
        return trim(trim($value),'\'');
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


    
}