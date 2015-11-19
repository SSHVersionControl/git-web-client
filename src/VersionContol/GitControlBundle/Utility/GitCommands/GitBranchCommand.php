<?php
// src/VersionContol/GitControlBundle/Utility/GitCommands/GitSyncCommand.php
namespace VersionContol\GitControlBundle\Utility\GitCommands;

use VersionContol\GitControlBundle\Utility\GitCommands\GitCommand;

/**
 * 
 * 
 */
class GitBranchCommand extends GitCommand {
    
    /**
     * Get current active Branch Name
     * If there is no commits (eg new repo) then branch name is 'NEW REPO'
     * This git command needs at least one commit before if show the correct branch name.
     *  
     * @return string The current branch name
     */
    public function getCurrentBranch(){
        $branchName = '';
        try{
            //$branchName =  $this->runCommand('git rev-parse --abbrev-ref HEAD');
            $branchName =  $this->runCommand('git symbolic-ref --short -q HEAD');
        }catch(\RuntimeException $e){
            if($this->getObjectCount() == 0){
                $branchName = 'NEW REPO';
            }
        }
        
        return $branchName;
        

    }
    
    /**
     * List all of the branches in your repository.
     * To list remote branches you may have to do a git fetch to 
     * get lastest changes.
     * 
     * @param boolean $local Flag to list local branches only
     * @return type
     */
    public function getBranches($local = false){
        
        $command = 'git for-each-ref "--format=\'%(refname:short)\'"';
        if($local === true){
            $command .= ' '.escapeshellarg("refs/heads/");
        }
        
        $localBranches = $this->runCommand($command);
         
         return $this->splitOnNewLine($localBranches,true);
    }
    
    /**
     * List all of the branches in your repository.
     * To list remote branches you may have to do a git fetch to 
     * get lastest changes.
     * 
     * @param boolean $local Flag to list local branches only
     * @return type
     */
    public function getRemoteBranches(){
        
        $command = 'git for-each-ref "--format=\'%(refname:short)\'"';
 
        $command .= ' '.escapeshellarg("refs/remotes/");
        
        
        $localBranches = $this->runCommand($command);
         
         return $this->splitOnNewLine($localBranches,true);
    }
    
    /**
     * 
     * @return array
     */
    public function getBranchRemoteListing(){
        $gitBranches = array();
        
        $localBranches = $this->getBranches(true);
        
        $remoteBranches = $this->getRemoteBranches();
        
        foreach ($remoteBranches as $branchName){
            $branchParts = explode('/',$branchName);
            if(in_array($branchParts[1], $localBranches)){
                $gitBranches[] = array('name' => $branchName, 'local' => true);
            }else{
                $gitBranches[] = array('name' => $branchName, 'local' => false); 
            }
        }
        
        return $gitBranches;
    }

    
    
    /**
     * Creates a new branch. It's important to understand that branches are just pointers to commits. 
     * When you create a branch, all Git needs to do is create a new pointer—it doesn’t change the 
     * repository in any other way.
     * 
     * @param string $branchName Name of new branch
     * @param Boolean $switchToBranch If true the new branch is checked out.
     * @return string command response
     */
    public function createLocalBranch($branchName,$switchToBranch = false){
        if($this->validateBranchName($branchName)){
            $output = $this->runCommand(sprintf('git branch "%s"',$branchName));

            if($switchToBranch){
                $output .= $this->runCommand(sprintf('git checkout %s 2>&1',  escapeshellarg($branchName)));;
            }
        }else{
            throw new \Exception('This is not a valid branch name');

        }
        
        return $output;
    }
    
    /**
     * Creates a new branch from a remote branch. It's important to understand that branches are just pointers to commits. 
     * When you create a branch, all Git needs to do is create a new pointer—it doesn’t change the 
     * repository in any other way.
     * 
     * @param string $branchName Name of new branch
     * @param Boolean $switchToBranch If true the new branch is checked out.
     * @return string command response
     */
    public function createBranchFromRemote($branchName,$remoteBranchName, $switchToBranch = false){
        if($this->validateBranchName($branchName)){
            $output = $this->runCommand(sprintf('git branch %s %s 2>&1',$branchName, $remoteBranchName));

            if($switchToBranch){
                $output .= $this->runCommand(sprintf('git checkout %s 2>&1',  escapeshellarg($branchName)));;
            }
        }else{
            throw new \Exception('This is not a valid branch name');

        }
        
        return $output;
    }
    
    
    
    /**
     * Validates Branch name. Checks if a branch name is allowed
     * 
     * @param string $branchName Name of new branch
     * @return Boolean true if valid branch name
     */
    public function validateBranchName($branchName){
        $output = $this->runCommand(sprintf('(git check-ref-format "refs/heads/%s");echo -e "\n$?"',$branchName));

        if(trim($output) == 1){
            return false;
        }

        return true;
    }
    
    /**
     * Rename the current branch 
     * 
     * @param string $branchName
     * @return string command output
     * @throws \Exception
     */
    public function renameCurrentBranch($branchName){
        $output = '';
        if($this->validateBranchName($branchName)){
            $output = $this->runCommand(sprintf('git branch -m "%s"',$branchName));
        }else{
            throw new \Exception('This is not a valid branch name');

        }
        
        return $output;
    }
    
    /**
     * The git checkout command lets you navigate between the branches created by git branch. 
     * Checking out a branch updates the files in the working directory to match the version 
     * stored in that branch, and it tells Git to record all new commits on that branch. 
     * 
     * @param string $branchName Name of new branch
     * @param Boolean $switchToBranch If true the new branch is checked out.
     * @return string command response
     */
    public function checkoutBranch($branchName){
        
        return $this->runCommand(sprintf('git checkout %s 2>&1',  escapeshellarg($branchName)));
        
    }
    
    /**
     * Deletes Branch with branch name. Setting $forceDelete equals false is a “safe” operation in that Git prevents you from 
     * deleting the branch if it has unmerged changes. 
     * Setting $forceDelete equals true force delete the specified branch, even if it has unmerged changes. This is the command to use 
     * if you want to permanently throw away all of the commits associated with a particular line of development. Use with caution
     * 
     * @param string $branchName Name of branch to delete
     * @param boolean $forceDelete Flag to delete branch, even if it has unmerged changes.
     * 
     * @return string command response
     */
    public function deleteBranch($branchName,$forceDelete = false){
        $currentBranch = $this->getCurrentBranch();
        if($branchName === $currentBranch){
            throw new \Exception('You cannot delete the current branch. Please checkout a different branch before deleting.');
        }
        if($forceDelete === true){
            $deleteFlag = '-D';
        }else{
             $deleteFlag = '-d';
        }
        return $this->runCommand(sprintf('git branch '.$deleteFlag.' %s 2>&1',  escapeshellarg($branchName)));
    }
    
   
    
    /**
     * Merges current branch with branch of name
     * 
     * @param string $branchName Name of branch to delete
     * @return string command response
     */
    public function mergeBranch($branchName){
        $currentBranch = $this->getCurrentBranch();
        if($branchName === $currentBranch){
            throw new \Exception('You cannot merge a branch with itself. Please checkout a different branch before trying to merge.');
        }
        return $this->runCommand(sprintf('git merge %s 2>&1',  escapeshellarg($branchName)));
    }
    
    /**
     * Fetch all branches from all remote repositories. 
     * 
     * @param remote $remote Name of remote Repository
     * @return string command response
     */
    public function fetchAll(){
        return $this->runCommand('git fetch --all 2>&1');
    }
    
}
