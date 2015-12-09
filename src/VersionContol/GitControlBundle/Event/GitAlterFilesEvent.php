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
    private $projectEnviroment;
    
    public function __construct($projectEnviroment) {
        $this->projectEnviroment = $projectEnviroment;
    }
 
    
}