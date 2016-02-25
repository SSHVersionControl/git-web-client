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
}
