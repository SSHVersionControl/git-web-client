<?php
// src/VersionContol/GitControlBundle/Utility/GitCommands/GitSyncCommand.php
namespace VersionContol\GitControlBundle\Utility\GitCommands;

use VersionContol\GitControlBundle\Utility\GitCommands\GitCommand;


/**
 * The git init command creates a new Git repository. It can be used to convert 
 * an existing, unversioned project to a Git repository or initialize a new empty repository. 
 * Most of the other Git commands are not available outside of an initialized 
 * repository, so this is usually the first command you’ll run in a new project.
 * 
 * Executing git init creates a .git subdirectory in the project root, which contains all 
 * of the necessary metadata for the repo. Aside from the .git directory, an existing 
 * project remains unaltered
 * 
 */
class GitInitCommand extends GitCommand {
    
    
    /**
     * Transform the current directory into a Git repository. This adds a .git folder 
     * to the current directory and makes it possible to start recording revisions of 
     * the project.
     * 
     * @return string command response
     */
    public function initRepository(){
        $response = $this->runCommand('git init');
        return $response;
    } 
    
    /**
     * Clone the repository located at <repo> onto the local machine. The original
     *  repository can be located on the local filesystem or on a remote machine
     *  accessible via HTTP or SSH.
     * @return string command response
     */
    public function cloneRepository($repo){
        $response = $this->runCommand('git clone');
        return $response;
    } 
    
}
