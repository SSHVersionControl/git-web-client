<?php
// src/Acme/UserBundle/Entity/User.php
namespace VersionContol\GitControlBundle\Utility;


use Symfony\Component\Process\Process;
use VersionContol\GitControlBundle\Entity\GitFile;
use VersionContol\GitControlBundle\Entity\GitLog;

use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
Use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use VersionContol\GitControlBundle\Entity\Project;
use VersionContol\GitControlBundle\Utility\SshProcess;

/**
 */
class GitCommands 
{
    
    protected $gitPath;
    
    /**
     * @var AuthorizationChecker
     */
    protected $securityContext;
    
    /**
     * The git project entity
     * @var Project
     */
    protected $project;
    
    /**
     *
     * @var type Git Status Hash.
     * Used to make sure no changes has occurred since last check 
     * @var string hash
     */
    protected $statusHash;

    /**
     * 
     * @param AuthorizationChecker $securityContext
     */
    public function __construct($securityContext)
    {
        $this->securityContext = $securityContext;
        
    }
    
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
            $branchName =  $this->runCommand('git rev-parse --abbrev-ref HEAD');
        }catch(\RuntimeException $e){
            if($this->getObjectCount() == 0){
                $branchName = 'NEW REPO';
            }
        }
        
        return $branchName;
        

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
     * Gets the size of the git repo
     * The command returns data in the format:
     *  3251 objects, 15308 kilobytes
     * @return integer The size of the git repo
     */
    public function getSize(){
        $result = $this->runCommand('git count-objects');
        $splits = explode(',',$result);
        //0 = object count 1 = size
        $objects = explode(' ',$splits[1]);
        $size = trim($objects[0]);
        
        return $size;
    }
    
    /**
     * Array of local branches
     * @return type
     */
    public function getLocalBranches(){
         $localBranches = $this->runCommand("git for-each-ref --format='%(refname:short)'  refs/heads/");
         return $this->splitOnNewLine($localBranches);
    }
    
    /**
     * Creates a new branch
     * @param string $branchName Name of new branch
     * @param Boolean $switchToBranch If true the new branch is checked out.
     * @return string command response
     */
    public function createLocalBranch($branchName,$switchToBranch = false){
        if($this->validateBranchName($branchName)){
            $output = $this->runCommand(sprintf('git branch "%s"',$branchName));

            if($switchToBranch){
                $output .= $this->runCommand(sprintf('git checkout "%s"',$branchName));
            }
        }else{
            throw new \Exception('This is not a valid branch name');

        }
        
        return $output;
    }
    
    /**
     * Validates Branch name.
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
     * Creates a new branch
     * @param string $branchName Name of new branch
     * @param Boolean $switchToBranch If true the new branch is checked out.
     * @return string command response
     */
    public function checkoutBranch($branchName){
        
        return $this->runCommand(sprintf('git checkout %s 2>&1',  escapeshellarg($branchName)));
        
    }


    /**
     * Gets the git log (history) of commits
     * Currenly limits to the last 20 commits.
     * @return GitLog|array
     */
    public function getLog($count = 20, $branch = 'master'){
        $logs = array();
        try{
            $logData = $this->runCommand('git --no-pager log --pretty=format:"%H | %h | %T | %t | %P | %p | %an | %ae | %ad | %ar | %cn | %ce | %cd | %cr | %s" -'.intval($count).' '.$branch);
        }catch(\RuntimeException $e){
            if($this->getObjectCount() == 0){
                return $logs;
            }else{
                //Throw exception
            }
        }

        $lines = $this->splitOnNewLine($logData);

        if(is_array($lines) && count($lines) > 0){
            foreach($lines as $line){
                if(trim($line)){
                    $logs[] = new GitLog($line);
                }
            }
        }
        
        return $logs;
    }
    
    /**
     * Gets a commit log by commit hash and branch
     * 
     * @param string $commitHash
     * @param string $branch
     * @return GitLog
     */
    public function getCommitLog($commitHash, $branch = 'master'){
        $log = null;
        $logData = $this->runCommand('git --no-pager log --pretty=format:"%H | %h | %T | %t | %P | %p | %an | %ae | %ad | %ar | %cn | %ce | %cd | %cr | %s" -1 '.$commitHash.' '.$branch);
        $lines = $this->splitOnNewLine($logData);

        if(is_array($lines) && count($lines) > 0){
            foreach($lines as $line){
                if(trim($line)){
                    $log = new GitLog($line);
                }
            }
        }
        
        return $log;
    }
    
    /**
     * Array of local branches
     * @return type
     */
    public function getCommitDiff($commitHash){
         $diffString = $this->runCommand("git --no-pager show  --oneline ".escapeshellarg($commitHash));
         $diffParser = new GitDiffParser($diffString);
         $diffs = $diffParser->parse(); 
         return $diffs;
    }
    
    /**
     * Gets all files that need to be commited
     * 
     * @return array Array of GitFile objects
     */
    public function getFilesToCommit(){
        $stausData = $this->getStatus();
        $this->statusHash = hash('md5',$stausData);
        $files = $this->processStatus($stausData);
        return $files;
    }
    
    /**
     * Git status command
     * Response:
     *  D feedback.html
     *  ?? time-selectors/work.html
     * 
     * @return string Command Response
     */
    public function getStatus(){
        return $this->runCommand('git status -u --porcelain');
    }
    
    /**
     * $ git remote
     * origin
     * 
     * @return array() Array of remote names
     */
    public function getRemotes(){
        
        $remotes = $this->splitOnNewLine($this->runCommand('git remote'));
        return $remotes;
    }
    
    /**
     * Get remote origins with url
     * $ git remote -v
     * origin	https://github.com/schacon/ticgit (fetch)
     * origin	https://github.com/schacon/ticgit (push)
     * pb	https://github.com/paulboone/ticgit (fetch)
     * pb	https://github.com/paulboone/ticgit (push)
     * 
     * @return array eg (array(0 => "origin", 1 => "https://github.com/schacon/ticgit", 2 => "(push)")
     */
    public function getRemoteVersions(){
        $remotes = array();
        
        $lines = $this->splitOnNewLine($this->runCommand('git remote -v'));

        if(count($lines) >= 2){
            for($i = 1; $i < count($lines); $i+=2){
                $parts = preg_split('/\s+/', $lines[$i]);
                if($parts[2] == "(push)"){
                    $remotes[] = $parts;
                }
            }
        }
        return $remotes;
        
    }
    
    /**
     * Stages the file to be committed. 
     * Currently supports adding and removing file.
     * 
     * @TODO Make it more effecient
     * @param string $file path to file to commit
     */
    public function stageFile($file){
         $gitFiles = $this->getFilesToCommit();
         
         //Validated that this status is same as previous
         $deleteFiles = array();
         $addFiles = array();
         foreach($gitFiles as $fileEntity){
             if($fileEntity->getPath1() == $file){
                 if($fileEntity->getWorkTreeStatus() == 'D' ){
                     $deleteFiles[] = escapeshellarg($file);
                 }else{
                     $addFiles[] = escapeshellarg($file);
                 }
             }
         }
         
         //Run the commands once for add and delete
         if(count($deleteFiles) > 0){
             $this->runCommand('git rm '.implode(' ',$deleteFiles));
         }
         
         if(count($addFiles) > 0){
             $this->runCommand('git add '.implode(' ',$addFiles));
         }
         
         
    }
    
    /**
     * Commits any file that was been staged
     *  
     * @param string $message
     * @return string response
     */
    public function commit($message){
        $user = $this->securityContext->getToken()->getUser();
        $author = $user->getName().' <'.$user->getEmail().'>';
        
        return $this->runCommand('git commit -m '.escapeshellarg($message).' --author='.escapeshellarg($author).''); 
        
    }
    
    /**
     * Push changes to the remote server
     * 
     * @param remote $remote The remote server to push to eg origin
     * @param string $branch The branch to push to the remote server eg master
     * @return string command response
     */
    public function push($remote,$branch){
        return $this->runCommand(sprintf('git push %s %s 2>&1',$remote,$branch));
    }
    
    /**
     * Pull changes to the remote server
     * 
     * @param remote $remote The remote server to push to eg origin
     * @param string $branch The branch to push to the remote server eg master
     * @return string command response
     */
    public function pull($remote,$branch){
        return $this->runCommand(sprintf('git pull %s %s 2>&1',$remote,$branch));
    }
    
    /**
     * Wrapper function to run shell commands. Supports local and remote commands
     * depending on the project details
     * 
     * @param string $command command to run
     * @return string Result of command
     * @throws \RuntimeException
     */
    protected function runCommand($command){
        
        $fullCommand = sprintf('cd %s ; %s',$this->gitPath,$command);
        if($this->project->getSsh() === true){
            $sshProcess = new SshProcess();
            $sshProcess->run(array($fullCommand),$this->project->getHost(),$this->project->getUsername(),22,$this->project->getPassword());
            return $sshProcess->getStdout();
        }else{
            $process = new Process($fullCommand);
            $process->run();

            // executes after the command finishes
            if (!$process->isSuccessful()) {
                throw new \RuntimeException($process->getErrorOutput());
            }
            
            return $process->getOutput();
        }
    }
    
    /**
     * Process the git status data into GitFile objects
     * 
     * @param string $stausData
     * @return array Array of GitFile objects
     */
    protected function processStatus($stausData){
        $files = array();
        
        $lines = $this->splitOnNewLine($stausData,false);

        if(is_array($lines) && count($lines) > 0){
            foreach($lines as $line){
                if(trim($line)){
                    $files[] = new GitFile($line,$this->gitPath);
                }
            }
        }

        return $files;
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
    public function setGitPath($gitPath) {
        $this->gitPath = rtrim(trim($gitPath),'/');
        return $this;
    }
    
    /**
     * Gets the project entity
     * @return Project
     */
    public function getProject() {
        return $this->project;
    }

    /**
     * Sets the project entity
     * @param Project $project
     */
    public function setProject(Project $project) {
        $this->project = $project;
        $this->setGitPath($project->getPath());
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
            return array_map('trim',$lines); 
        }else{
            return $lines; 
        }
    }

    /**
     * Get hash of git status
     * @return String hash
     */
    public function getStatusHash() {
        if(!$this->statusHash){
            $stausData = $this->getStatus();
            $this->statusHash = hash('md5',$stausData);
        }
        return $this->statusHash;
    }

    
}

