<?php
// src/Acme/UserBundle/Entity/User.php

namespace VersionContol\GitControlBundle\Entity;



/**
 * In the short-format, the status of each path is shown as
 * XY PATH1 -> PATH2
 * where PATH1 is the path in the HEAD, and the ` -> PATH2` part is shown only when PATH1 corresponds to a different path in the index/worktree (i.e. the file is renamed). The XY is a two-letter status code.
 * 
 * The fields (including the ->) are separated from each other by a single space. If a filename contains whitespace or other nonprintable characters, that field will be quoted in the manner of a C string literal: surrounded by ASCII double quote (34) characters, and with interior special characters backslash-escaped.
 * For paths with merge conflicts, X and Y show the modification states of each side of the merge. For paths that do not have merge conflicts, X shows the status of the index, and Y shows the status of the work tree. For untracked paths, XY are ??. Other status codes can be interpreted as follows:
 * ' ' = unmodified
 * M = modified
 * A = added
 * D = deleted
 * R = renamed
 * C = copied
 * U = updated but unmerged

Ignored files are not listed, unless --ignored option is in effect, in which case XY are !!.

X          Y     Meaning
-------------------------------------------------
          [MD]   not updated
M        [ MD]   updated in index
A        [ MD]   added to index
D         [ M]   deleted from index
R        [ MD]   renamed in index
C        [ MD]   copied in index
[MARC]           index and work tree matches
[ MARC]     M    work tree changed since index
[ MARC]     D    deleted in work tree
-------------------------------------------------
D           D    unmerged, both deleted
A           U    unmerged, added by us
U           D    unmerged, deleted by them
U           A    unmerged, added by them
D           U    unmerged, deleted by us
A           A    unmerged, both added
U           U    unmerged, both modified
-------------------------------------------------
?           ?    untracked
!           !    ignored
-------------------------------------------------
If -b is used the short-format status is preceded by a line
 */
class GitFile
{
    /**
     */
    protected $id;
    
    protected $fileType;
    
    protected $indexStatus;
    
    protected $workTreeStatus;
    
    protected $path1;
    
    protected $path2;
    
    protected $line;
    
    protected $gitPath;
    

    public function __construct($line,$gitPath){
        $this->line = $line;
        $this->gitPath = $gitPath;
        
        $path = substr(rtrim($line), 3);
        $paths = explode (' -> ', $path);
        if(count($paths) == 2){
            $this->path1 = $paths[0];
            $this->path2 = $paths[1];
        }else{
             $this->path1 = $paths[0];
        }

        //$this->fileType = filetype($gitPath.'/'.$this->path1);
        
        $this->indexStatus = $line[0];
        $this->workTreeStatus = $line[1]; 

    }
    
    public function getFileType() {
        return $this->fileType;
    }

    public function getIndexStatus() {
        return $this->indexStatus;
    }

    public function getWorkTreeStatus() {
        return $this->workTreeStatus;
    }

    public function getPath1() {
        return $this->path1;
    }

    public function getPath2() {
        return $this->path2;
    }

    public function setFileType($fileType) {
        $this->fileType = $fileType;
    }

    public function setIndexStatus($indexStatus) {
        $this->indexStatus = $indexStatus;
    }

    public function setWorkTreeStatus($workTreeStatus) {
        $this->workTreeStatus = $workTreeStatus;
    }

    public function setPath1($path1) {
        $this->path1 = $path1;
    }

    public function setPath2($path2) {
        $this->path2 = $path2;
    }


    
    
}

