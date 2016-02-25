<?php
namespace VersionContol\GitControlBundle\Utility\GitCommands\Command;

use VersionContol\GitControlBundle\Utility\GitCommands\GitCommand;
use VersionContol\GitControlBundle\Entity\Project;

/**
 * Abstract Class for Git commands
 *
 * @author Paul Schweppe
 */
class AbstractGitCommand implements InterfaceGitCommand{
    
    protected $command;
    
    public function __construct(GitCommand $command) {
        $this->command = $command;
    }
    
    /**
     * Sets the project entity
     * @param Project $project
     */
    public function setProject(Project $project) {
        $this->command->setProject($project);
        return $this;
    }
    
    public function runCommand($command){
        return $this->command->runCommand($command);
    }
    
    /**
     * Gets the number of objects in git repo
     * The command returns data in the format:
     *  3251 objects, 15308 kilobytes
     * @return integer The number of objects
     */
    public function getObjectCount(){
        $result = $this->runCommand('git count-objects');
        $splits = explode(',',$result);
        //0 = object count 1 = size
        $objects = explode(' ',$splits[0]);
        $objectCount = $objects[0];
        
        return $objectCount;
    }
    
    /**
     * Splits a block of text on newlines and returns an array
     *  
     * @param string $text Text to split
     * @param boolean $trimSpaces If true then each line is trimmed of white spaces. Default true. 
     * @return array Array of lines
     */
    public function splitOnNewLine($text,$trimSpaces = true){
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
    
    public function addListener($eventName, $listener)
    {
        $this->command->getEventDispatcher()->addListener($eventName, $listener);
    }
    
    protected function triggerGitAlterFilesEvent($eventName = 'git.alter_files'){
        $event = new GitAlterFilesEvent($this->command->getProjectEnvironment(),array());
        $this->triggerEvent($eventName, $event);
    }
    
    protected function triggerEvent($eventName,$event){
        $this->command->dispatcher->dispatch($eventName, $event);
    }
}
