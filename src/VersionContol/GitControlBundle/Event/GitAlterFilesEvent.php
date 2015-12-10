<?php
namespace VersionContol\GitControlBundle\Event;

/**
 * Description of GitFilesChangedEvent
 *
 * @author paul
 */
use Symfony\Component\EventDispatcher\Event;
 
class GitAlterFilesEvent extends Event
{
    /**
     *
     * @var VersionContol\GitControlBundle\Entity\ProjectEnvironment
     */
    private $projectEnviroment;
    
    private $filesAltered = array();
    
    /**
     * 
     * @param VersionContol\GitControlBundle\Entity\ProjectEnvironment $projectEnviroment
     */
    public function __construct(\VersionContol\GitControlBundle\Entity\ProjectEnvironment $projectEnviroment, $files) {
        $this->projectEnviroment = $projectEnviroment;
        $this->filesAltered = $files;
    }
    
    public function getProjectEnviroment() {
        return $this->projectEnviroment;
    }

    public function setProjectEnviroment(VersionContol\GitControlBundle\Entity\ProjectEnvironment $projectEnviroment) {
        $this->projectEnviroment = $projectEnviroment;
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