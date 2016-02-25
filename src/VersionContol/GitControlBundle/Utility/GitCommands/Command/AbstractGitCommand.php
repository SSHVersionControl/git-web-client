<?php
namespace VersionContol\GitControlBundle\Utility\GitCommands\Command;

use VersionContol\GitControlBundle\Utility\GitCommands\GitCommand;

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
}
