<?php
namespace VersionControl\GitCommandBundle\Event;

/**
 * Description of GitFilesChangedEvent
 *
 * @author paul
 */
use Symfony\Component\EventDispatcher\Event;
use VersionControl\GitCommandBundle\GitCommands\GitEnvironmentInterface;
 
class GitAlterFilesEvent extends Event
{
    /**
     *
     * @var VersionControl\GitCommandBundle\Entity\ProjectEnvironment
     */
    private $gitEnvironment;
    
    private $filesAltered = array();
    
    /**
     * 
     * @param GitEnvironmentInterface $gitEnvironment
     * @param List of files altered
     */
    public function __construct(GitEnvironmentInterface $gitEnvironment, $files) {
        $this->gitEnvironment = $gitEnvironment;
        $this->filesAltered = $files;
    }
    
    public function getGitEnvironment() {
        return $this->gitEnvironment;
    }

    public function setGitEnvironment(GitEnvironmentInterface $gitEnvironment) {
        $this->gitEnvironment = $gitEnvironment;
        return $this;
    }
    
    public function getFilesAltered() {
        return $this->filesAltered;
    }

    public function setFilesAltered($filesAltered) {
        $this->filesAltered = $filesAltered;
        return $this;
    }




 
    
}