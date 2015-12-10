<?php
namespace VersionContol\GitControlBundle\Utility\GitCommands;

use VersionContol\GitControlBundle\Entity\FileInfo;
use VersionContol\GitControlBundle\Entity\RemoteFileInfo;
use VersionContol\GitControlBundle\Entity\GitFile;
use VersionContol\GitControlBundle\Entity\GitLog;

use phpseclib\Net\SFTP;

/**
 * Description of GitFilesCommand
 *
 * @author fr_user
 */
class GitFilesCommand extends GitCommand {
    
    /**
     * List files in directory with git log
     * @param string $dir
     * @return array remoteFileInfo/fileInfo
     */
    public function listFiles($dir,$branch="master",$gitFilesOnly=false){
        $files = array();
        $fileList = $this->getFilesInDirectory($dir);

         foreach($fileList as $fileInfo){
             $fileLastLog = $this->getLog(1,$branch,$fileInfo->getGitPath());
    
             if(count($fileLastLog) > 0){
                 $fileInfo->setGitLog($fileLastLog[0]);
                 $files[] = $fileInfo;
             }elseif($gitFilesOnly === false){
                $files[] = $fileInfo;
            }
         }
          
         return $files;
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
    
    /**
     * Get files in directory locally and remotely 
     * 
     * @param string $dir full path to directory
     * @return array of files
     */
    public function getFilesInDirectory($dir){
        
         if($this->validPathStr($dir) === false){
             throw new \Exception('Directory path is not valid. Possible security issue.');
         }
         
         $files = array();
         $basePath = $this->addEndingSlash($this->projectEnvironment->getPath());
         $relativePath = $dir;
         if($relativePath){
              $relativePath = $this->addEndingSlash($relativePath);
         }
         
        
         if($this->projectEnvironment->getSsh() === true){
             //Remote Directory Listing
            $sftp = new SFTP($this->projectEnvironment->getHost(), 22);
            if (!$sftp->login($this->projectEnvironment->getUsername(), $this->projectEnvironment->getPassword())) {
                exit('Login Failed');
            }

            foreach($sftp->rawlist($basePath.$relativePath) as $filename => $fileData) {
                if($filename !== '.' && $filename !== '..' && $filename !== '.git'){
                    $fileData['fullPath'] = rtrim($relativePath,'/').'/'.$filename;
                    $fileData['gitPath'] = $relativePath.$filename;

                    $remoteFileInfo = new RemoteFileInfo($fileData);
                    $files[] = $remoteFileInfo;
                }
            }

         }else{
             //Local Directory Listing
            $directoryIterator = new \DirectoryIterator($basePath.$dir);
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
        
         if($this->projectEnvironment->getSsh() === true){
             //Remote Directory Listing
            $sftp = new SFTP($this->projectEnvironment->getHost(), 22);
            if (!$sftp->login($this->projectEnvironment->getUsername(), $this->projectEnvironment->getPassword())) {
                exit('Login Failed');
            }
            
            $fileContents = $sftp->get($file->getFullPath());

         }else{
            $fileContents = file_get_contents($file->getFullPath());
         }
         
         return $fileContents;
    }
    
    /**
    * Checks for malicious file paths.
    *
    * Returns TRUE if no '//', '..', '\' or control characters are found in the $theFile.
    * This should make sure that the path is not pointing 'backwards' and further doesn't contain double/back slashes.
    * So it's compatible with the UNIX style path strings valid for TYPO3 internally.
    *
    * @param string $theFile File path to evaluate
    * @return boolean TRUE, $theFile is allowed path string, FALSE otherwise
    * @see http://php.net/manual/en/security.filesystem.nullbytes.php
    * @todo Possible improvement: Should it rawurldecode the string first to check if any of these characters is encoded?
    */
    public function validPathStr($theFile) {
            if (strpos($theFile, '//') === FALSE && strpos($theFile, '\\') === FALSE && !preg_match('#(?:^\\.\\.|/\\.\\./|[[:cntrl:]])#u', $theFile)) {
                    return TRUE;
            }
            return FALSE;
    }
        
    
    public function setFilesPermissions($filePaths, $user = 7,$group = 7,$other= 5){
        $basePath = trim($this->addEndingSlash($this->projectEnvironment->getPath()));
        if($this->projectEnvironment->getSsh() === true){
             //Remote Directory Listing
            $sftp = $this->connectToSftp();

            $mode= '0'.$user.$group.$other;
            $permissions = octdec($mode);
            foreach($filePaths as $filepath){
                $this->runCommand(sprintf("chmod -R %s %s",$mode,$basePath.$filepath));
                //$sftp->chmod($permissions, $basePath.$filepath,true);
            }

        }else{
            //Run local chmod
        }
    }
    
    public function setFilesOwnerAndGroup($filePaths,$user ='www-data',$group = 'fr_user'){
        $basePath = trim($this->addEndingSlash($this->projectEnvironment->getPath()));
        if($this->projectEnvironment->getSsh() === true){
             //Remote Directory Listing
            foreach($filePaths as $filepath){
                $this->runCommand(sprintf("chown -R %s.%s %s",$user,$group,$basePath.$filepath));
            }  
        }else{
            //Run local chmod
        }
       
    }
    
    protected function connectToSftp(){
        $sftp = new SFTP($this->projectEnvironment->getHost(), 22);
        if (!$sftp->login($this->projectEnvironment->getUsername(), $this->projectEnvironment->getPassword())) {
            exit('Login Failed');
        }
        
        return $sftp;
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
            $command = 'git --no-pager log -m "--pretty=format:\'%H | %h | %T | %t | %P | %p | %an | %ae | %ad | %ar | %cn | %ce | %cd | %cr | %s\'" -'.intval($count).' '.escapeshellarg(trim($branch));
            
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
}
