<?php
// src/VersionContol/GitControlBundle/Utility/GitCommands/GitUndoCommand.php
namespace VersionContol\GitControlBundle\Utility\GitCommands;

use VersionContol\GitControlBundle\Utility\GitCommands\GitCommand;

/**
 * 
 * 
 */
class GitUndoCommand extends GitCommand {
    
    /**
     * 
     * @return string command response
     */
    public function resetPullRequest(){
        return $this->runCommand('git reset --hard ORIG_HEAD');
    }
    
    /**
     * Reverts commit but keeps the files unchanged.
     * @return string command response
     */
    public function undoCommit(){
        return $this->runCommand('git reset --soft HEAD~1');
    }
    
    /**
     * 
     * @return string command response
     */
    public function undoCommitHard(){
        return $this->runCommand('git reset --hard HEAD~1');
    }
    
    /**
     * Update all files in the working directory to match the specified commit. 
     * You can use either a commit hash or a tag as the $commitHash argument. 
     * This will put you in a detached HEAD state.
     */
    public function checkoutCommit($commitHash = 'HEAD'){
        $response = $this->runCommand(sprintf('git checkout %s 2>&1',escapeshellarg($commitHash)));
        
        //Trigger file alter Event
        $this->triggerGitAlterFilesEvent();
        
        return $response;       
    }
    
}
