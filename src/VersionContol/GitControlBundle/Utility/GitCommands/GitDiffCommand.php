<?php
namespace VersionContol\GitControlBundle\Utility\GitCommands;

use VersionContol\GitControlBundle\Utility\GitDiffParser;
use VersionContol\GitControlBundle\Entity\GitCommitFile;
use VersionContol\GitControlBundle\Entity\Collections\GitCommitFileCollection;

/**
 * Description of GitFilesCommand
 *
 * @author fr_user
 */
class GitDiffCommand extends GitCommand {

    
    /**
     * Get diff based on Commit hash id
     * @return array()
     */
    public function getCommitDiff($commitHash){
         $diffString = $this->runCommand("git --no-pager show  --oneline ".escapeshellarg($commitHash));
         $diffParser = new GitDiffParser($diffString);
         $diffs = $diffParser->parse(); 
         return $diffs;
    }
    
    /**
     * Get diff on a file
     * @return array()
     */
    public function getDiffFile($filename){
         $diffString = $this->runCommand("git --no-pager diff  --oneline ".escapeshellarg($filename)." 2>&1");
         $diffParser = new GitDiffParser($diffString);
         $diffs = $diffParser->parse(); 
         return $diffs;
    }
    
    /**
     * Returns a list of files effected by a commit.
     * 
     * @return array() Array of file paths
     */
    public function getFilesInCommit($commitHash){
         $response = $this->runCommand("git diff-tree --no-commit-id --name-status -r ".escapeshellarg($commitHash)."");
         $responseLines = $this->splitOnNewLine($response);
         $files = new GitCommitFileCollection();
         foreach($responseLines as $line){
             $files->addGitCommitFile((new GitCommitFile($line)));
         }
         return $files;
    }
    
    
    
}