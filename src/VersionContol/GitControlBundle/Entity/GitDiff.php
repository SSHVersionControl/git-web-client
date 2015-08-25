<?php
// src/VersionContol/GitControlBundle/Entity/GitDiff.php

namespace VersionContol\GitControlBundle\Entity;


/**
 * Git Diff Object
 *
 * @author fr_user
 */
class GitDiff {
    
    const NEWFILE = 'new';
    
    const DELETDFILE = 'deleted';
    
    const MODIFIEDFILE = 'modified';
    
    /**
     * File before commit eg From File
     * @var string 
     */
    protected $fileA;
    
    /**
     * File after commit eg To file
     * @var string 
     */
    protected $fileB;
    
    /**
     * Lines in Commit
     * @var array of GitDiffLines 
     */
    protected $diffLines;
    
    public function __construct() {
        
    }

    /**
     * Get file from path
     * @return string
     */
    public function getFileA() {
        return $this->fileA;
    }

    /**
     * Set file to 
     * @param type $fileA
     * @return \VersionContol\GitControlBundle\Entity\GitDiff
     */
    public function setFileA($fileA) {
        $this->fileA = $fileA;
        return $this;
    }
    
    /**
     * Get file to path
     * @return string
     */
    public function getFileB() {
        return $this->fileB;
    }

    /**
     * Set file From
     * @param type $fileB
     * @return \VersionContol\GitControlBundle\Entity\GitDiff
     */
    public function setFileB($fileB) {
        $this->fileB = $fileB;
        return $this;
    }

    /**
     * Gets array of Git Diff lines
     * @return array Of GitDiffLines
     */
    public function getDiffLines() {
        return $this->diffLines;
    }

    /**
     * Sets git diff lines
     * @param array $diffLines
     * @return \VersionContol\GitControlBundle\Entity\GitDiff
     */
    public function setDiffLines($diffLines) {
        $this->diffLines = $diffLines;
        return $this;
    }
    
    /**
     * Gets the diff file name based on file a and b
     * Removes a/ or b/ from start of file path
     * @return string files path
     */
    public function getFileName(){
        if($this->fileA == '/dev/null'){
            $file = $this->fileB;
        }else{
            $file = $this->fileA;
        }
        
        return substr($file ,2);
    }
    
    /**
     * Gets the status of the diff eg new, deleted or modified
     * @return string constant
     */
    public function getStatus(){
        if($this->fileA == '/dev/null'){
            $status = self::NEWFILE;
        }elseif($this->fileB == '/dev/null'){
            $status = self::DELETDFILE;
        }else{
            $status = self::MODIFIEDFILE;
        }
        
        return $status;
    }
    




}
