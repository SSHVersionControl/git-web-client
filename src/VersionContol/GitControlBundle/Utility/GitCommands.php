<?php
// src/Acme/UserBundle/Entity/User.php
namespace VersionContol\GitControlBundle\Utility;


use Symfony\Component\Process\Process;
use Symfony\Component\Process\ProcessBuilder;
use VersionContol\GitControlBundle\Entity\GitFile;
use VersionContol\GitControlBundle\Entity\GitLog;
use VersionContol\GitControlBundle\Entity\FileInfo;
use VersionContol\GitControlBundle\Entity\RemoteFileInfo;

use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;
Use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use VersionContol\GitControlBundle\Entity\Project;
use VersionContol\GitControlBundle\Utility\SshProcess;

use phpseclib\Net\SFTP;

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
         $localBranches = $this->runCommand('git for-each-ref "--format=\'%(refname:short)\'"  '.escapeshellarg("refs/heads/"));
         
         return $this->splitOnNewLine($localBranches,true);
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
                $output .= $this->runCommand(sprintf('git checkout %s 2>&1',  escapeshellarg($branchName)));;
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
     * Deletes Branch with branch name
     * 
     * @param string $branchName Name of branch to delete
     * @return string command response
     */
    public function deleteBranch($branchName){
        $currentBranch = $this->getCurrentBranch();
        if($branchName === $currentBranch){
            throw new \Exception('You cannot delete the current branch. Please checkout a different branch before deleting.');
        }
        return $this->runCommand(sprintf('git branch -d %s 2>&1',  escapeshellarg($branchName)));
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
     * Find the last commit that both branches contain
     * 
     * @param string $branchName1
     * @param string $branchName2
     * @return string The commit id (Long)
     */
    public function lastCommitBothContains($branchName1,$branchName2){
        return $this->runCommand(sprintf('git merge-base %s %s 2>&1',  escapeshellarg($branchName1),  escapeshellarg($branchName2)));
    }


    /**
     * Gets the git log (history) of commits
     * Currenly limits to the last 20 commits.
     * @return GitLog|array
     */
    public function getLog($count = 20, $branch = 'master', $fileName = false){
        $logs = array();
        $logData = '';
        try{
            //$logData = $this->runCommand('git --no-pager log --pretty=format:"%H | %h | %T | %t | %P | %p | %an | %ae | %ad | %ar | %cn | %ce | %cd | %cr | %s" -'.intval($count).' '.$branch);
            $command = 'git --no-pager log "--pretty=format:\'%H | %h | %T | %t | %P | %p | %an | %ae | %ad | %ar | %cn | %ce | %cd | %cr | %s\'" -'.intval($count).' '.escapeshellarg(trim($branch));
            
            if($fileName !== false){
                $command .= ' '.escapeshellarg($fileName);
            }
            $logData = $this->runCommand($command);
            
     
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
   
        $logData = $this->runCommand('git --no-pager log "--pretty=format:\'%H | %h | %T | %t | %P | %p | %an | %ae | %ad | %ar | %cn | %ce | %cd | %cr | %s\'" -1 '.escapeshellarg($commitHash));
//print_r($logData);
        //$logData = $this->runCommand('git \'--no-pager log\' \'--pretty=format:"%H | %h | %T | %t | %P | %p | %an | %ae | %ad | %ar | %cn | %ce | %cd | %cr | %s"\' -1 \''.$commitHash.'\' \''.$branch.'\'');
        $lines = $this->splitOnNewLine($logData);
//print_r($lines);
        if(is_array($lines) && count($lines) > 0){
            foreach($lines as $line){
                if(trim($line)){
                    $log = new GitLog($line);
                }
            }
        }
        //print_r($log);
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
     * Get diff on a file
     * @return type
     */
    public function getDiffFile($filename){
         $diffString = $this->runCommand("git --no-pager diff  --oneline ".escapeshellarg($filename)." 2>&1");
         $diffParser = new GitDiffParser($diffString);
         $diffs = $diffParser->parse(); 
         return $diffs;
    }
    
    /**
     * 
     * @param String $remote
     * @param String $branch
     * @return type
     */
    public function getDiffRemoteBranch($remote,$branch){
        $diffString = $this->runCommand("git --no-pager diff  --oneline ".escapeshellarg($branch)." ".escapeshellarg($remote)."/".escapeshellarg($branch)." 2>&1");
        $diffParser = new GitDiffParser($diffString);
        $diffs = $diffParser->parse(); 
        return $diffs;
    }
    
    /**
     * List files in directory with git log
     * @param string $dir
     * @return array remoteFileInfo/fileInfo
     */
    public function listFiles($dir,$branch="master"){
        $files = array();
        $fileList = $this->getFilesInDirectory($dir);

         foreach($fileList as $fileInfo){
             $fileLastLog = $this->getLog(1,$branch,$fileInfo->getGitPath());
    
             if(count($fileLastLog) > 0){
                 $fileInfo->setGitLog($fileLastLog[0]);
                
             }else{
                 
             }
             $files[] = $fileInfo;
         }
          
         return $files;
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
     * Stage files for commit.
     * @param array $files
     */
    public function stageFiles(array $files){
         $gitFiles = $this->getFilesToCommit();
         
         //Validated that this status is same as previous
         $deleteFiles = array();
         $addFiles = array();
          
         $flippedFiles = array_flip($files);
         
         foreach($gitFiles as $fileEntity){
             if(isset($flippedFiles[$fileEntity->getPath1()])){
                 if($fileEntity->getWorkTreeStatus() == '!' || $fileEntity->getWorkTreeStatus() == '!' ){
                     //do Nothing
                 }elseif($fileEntity->getWorkTreeStatus() == 'D' ){
                     $deleteFiles[] = escapeshellarg($fileEntity->getPath1());
                 }else{
                     $addFiles[] = escapeshellarg($fileEntity->getPath1());
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
     * Stages the file to be committed. 
     * Currently supports adding and removing file.
     * 
     * @TODO Make it more effecient
     * @param string $file path to file to commit
     */
    public function stageFile($file){
         $this->stageFiles(array($file));
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
        
        return $this->runCommand('git commit -m '.escapeshellarg($message).' --author='.escapeshellarg($author)); 
        
    }
    
    /**
     * Push changes to the remote server
     * 
     * @param remote $remote The remote server to push to eg origin
     * @param string $branch The branch to push to the remote server eg master
     * @return string command response
     */
    public function push($remote,$branch){
        return $this->runCommand(sprintf('git push %s %s 2>&1',escapeshellarg($remote),escapeshellarg($branch)));
    }
    
    /**
     * Pull changes to the remote server
     * 
     * @param remote $remote The remote server to push to eg origin
     * @param string $branch The branch to push to the remote server eg master
     * @return string command response
     */
    public function pull($remote,$branch){
        //return $this->runCommand(sprintf('git pull %s %s "2>&1"',escapeshellarg($remote),escapeshellarg($branch)));
        return $this->runCommand(sprintf('git pull %s %s 2>&1',escapeshellarg($remote),escapeshellarg($branch)));
    }
    
    /**
     * Fetch changes from the remote server
     * 
     * @param remote $remote The remote server to push to eg origin
     * @param string $branch The branch to push to the remote server eg master
     * @return string command response
     */
    public function fetch($remote,$branch){
        //return $this->runCommand(sprintf('git pull %s %s "2>&1"',escapeshellarg($remote),escapeshellarg($branch)));
        return $this->runCommand(sprintf('git fetch %s %s 2>&1',escapeshellarg($remote),escapeshellarg($branch)));
    }
    
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
     * Changes the message of the last commit.
     * @param String $message
     * @return string command response
     */
    public function updateLastCommitMessage($message){
        $user = $this->securityContext->getToken()->getUser();
        $author = $user->getName().' <'.$user->getEmail().'>';
        
        return $this->runCommand('git commit -m '.escapeshellarg($message).' --author='.escapeshellarg($author));
    }
    
    /**
     * # This will create three separate revert commits:
        git revert a867b4af 25eee4ca 0766c053

        # It also takes ranges. This will revert the last two commits:
        git revert HEAD~2..HEAD

        # Reverting a merge commit
        git revert -m 1 <merge_commit_sha>

        # To get just one, you could use `rebase -i` to squash them afterwards
        # Or, you could do it manually (be sure to do this at top level of the repo)
        # get your index and work tree into the desired state, without changing HEAD:
        git checkout 0d1d7fc32 .

        # Then commit. Be sure and write a good message describing what you just did
        git commit
     * 
     * git revert --no-commit 0766c053..HEAD
        git commit
     */
    public function revert($commitId){
       $this->runCommand('git revert -m --no-commit '.escapeshellarg($commitId).' HEAD');
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
        
        if($this->project->getSsh() === true){
            $fullCommand = sprintf('cd %s && %s',$this->gitPath,$command);
            $sshProcess = new SshProcess();
            $sshProcess->run(array($fullCommand),$this->project->getHost(),$this->project->getUsername(),22,$this->project->getPassword());
            return $sshProcess->getStdout();
        }else{
            if(is_array($command)){
                //$finalCommands = array_merge(array('cd',$this->gitPath,'&&'),$command);
                $builder = new ProcessBuilder($command);
                $builder->setPrefix('cd '.$this->gitPath.' && ');
                $process = $builder->getProcess();
                
            }else{
                $fullCommand = sprintf('cd %s && %s',$this->gitPath,$command);
                $process = new Process($fullCommand);
            }
            //return exec($fullCommand);
            //print_r($process->getCommandLine());
            $process->run();

            // executes after the command finishes
            if (!$process->isSuccessful()) {
                
                throw new \RuntimeException($process->getErrorOutput());
            }
            
            return $process->getOutput();
        }
    }
    
    /**
     * Get files in directory locally and remotely 
     * 
     * @param string $dir full path to directory
     * @return array of files
     */
    protected function getFilesInDirectory($dir){
         $files = array();
         
         $relativePath = substr($dir, strlen($this->project->getPath()));
         if($relativePath){
              $relativePath = $this->addEndingSlash($relativePath);
         }
        
         if($this->project->getSsh() === true){
             //Remote Directory Listing
            $sftp = new SFTP($this->project->getHost(), 22);
            if (!$sftp->login($this->project->getUsername(), $this->project->getPassword())) {
                exit('Login Failed');
            }

            foreach($sftp->rawlist($dir) as $filename => $fileData) {
                if($filename !== '.' && $filename !== '..' && $filename !== '.git'){
                    $fileData['fullPath'] = rtrim($dir,'/').'/'.$filename;
                    $fileData['gitPath'] = $relativePath.$filename;

                    $remoteFileInfo = new RemoteFileInfo($fileData);
                    $files[] = $remoteFileInfo;
                }
            }

         }else{
             //Local Directory Listing
            $directoryIterator = new \DirectoryIterator($dir);
            $directoryIterator->setInfoClass('\VersionContol\GitControlBundle\Entity\FileInfo');

            foreach ($directoryIterator as $fileInfo) {
                if(!$fileInfo->isDot() && $fileInfo->getFilename() !== '.git'){

                    $newFileInfo = $fileInfo->getFileInfo();
                    $newFileInfo->setGitPath($relativePath.$fileInfo->getFilename());

                    $files[] = $newFileInfo;
                }
            }
        }
        
        $this->sortFilesByDirectoryThenName($files);

         return $files;
    }
    
    public function readFile($file){
   
        $fileContents = '';
        
         if($this->project->getSsh() === true){
             //Remote Directory Listing
            $sftp = new SFTP($this->project->getHost(), 22);
            if (!$sftp->login($this->project->getUsername(), $this->project->getPassword())) {
                exit('Login Failed');
            }
            
            $fileContents = $sftp->get($file->getFullPath());

         }else{
            $fileContents = file_get_contents($file->getFullPath());
         }
         
         return $fileContents;
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
            return array_map(array($this,'trimSpaces'),$lines); 
        }else{
            return $lines; 
        }
    }
    
    public function trimSpaces($value){
        return trim(trim($value),'\'');
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
    
    /**
     * Adds Ending slash where needed for unix and windows paths
     * 
     * @param string $path
     * @return string
     */
    protected function addEndingSlash($path){
 
        $slash_type = (strpos($path, '\\')===0) ? 'win' : 'unix'; 
        $last_char = substr($path, strlen($path)-1, 1);
        if ($last_char != '/' and $last_char != '\\') {
            // no slash:
            $path .= ($slash_type == 'win') ? '\\' : '/';
        }

        return $path;
    }
    
    /**
     * Sort files by directory then name
     * @param array $fileArray
     */
    protected function sortFilesByDirectoryThenName(array &$fileArray){
         usort($fileArray, function($a, $b){
            if($a->isDir()){
                if($b->isDir()){
                    return strnatcasecmp ($a->getFilename(), $b->getFilename());
                }else{
                    return -1;
                }
            }else{
                if($b->isDir()){
                    return 1;
                }else{
                    return strnatcasecmp ($a->getFilename(), $b->getFilename());
                }
            }
        });
    } 

    
}

