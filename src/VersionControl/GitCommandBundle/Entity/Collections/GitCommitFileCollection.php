<?php
// src/VersionControl/GitCommandBundle/Entity/Collections/GitCommitFileCollection.php

/*
 * This file is part of the GitCommandBundle package.
 *
 * (c) Paul Schweppe <paulschweppe@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace VersionControl\GitCommandBundle\Entity\Collections;

use VersionControl\GitCommandBundle\Entity\GitCommitFile;

/**
 * Array of committed file with statistics on added, copied, deleted, renamed and modified 
 * 
 * @author Paul Schweppe <paulschweppe@gmail.com>
 */
class GitCommitFileCollection implements \Iterator{
    
    private $gitCommitFiles = [];
    
    private $index = 0;
    
    /**
     * Added (A) Count
     * @var integer; 
     */
    private $addedCount = 0;
         
    /**
     *  Copied (C), count
     * @var integer 
     */
    private $copiedCount = 0;
    
    /**
     * Deleted (D), count
     * @var integer 
     */
    private $deletedCount = 0;
    
    /**
     * Modified (M), count
     * @var integer 
     */
    private $modifiedCount = 0;
    
    /**
     * Renamed (R), count
     * @var integer 
     */
    private $renamedCount = 0;
    
    /**
     * Stores the count of other file type (i.e. regular file, symlink, submodule, …​) with status
     * changed (T),
     * Unmerged (U), 
     * Unknown (X),
     * Broken (B)
     * @var integer 
     */
    private $otherStatusCount = 0;

    public function current()
    {
        return $this->gitCommitFiles[$this->index];
    }

    public function next()
    {
        $this->index ++;
    }

    public function key()
    {
        return $this->index;
    }

    public function valid()
    {
        return isset($this->gitCommitFiles[$this->key()]);
    }

    public function rewind()
    {
        $this->index = 0;
    }

    public function reverse()
    {
        $this->gitCommitFiles = array_reverse($this->gitCommitFiles);
        $this->rewind();
    }
    
    
    public function addGitCommitFile(GitCommitFile $gitCommitFile) {
        $this->gitCommitFiles[] = $gitCommitFile;
        $this->processStatusCount($gitCommitFile->getStatusChange());
        return $this;
    }
    
    protected function processStatusCount($status){
        if($status === 'A'){
            $this->addedCount++;
        }elseif($status === 'C'){
             $this->copiedCount++;
        }elseif($status === 'D'){
             $this->deletedCount++;
        }elseif($status === 'M'){
             $this->modifiedCount++;
        }elseif($status === 'R'){
             $this->renamedCount++;
        }else{
             $this->otherStatusCount++;
        }
    }
    
    public function getAddedCount() {
        return $this->addedCount;
    }

    public function getCopiedCount() {
        return $this->copiedCount;
    }

    public function getDeletedCount() {
        return $this->deletedCount;
    }

    public function getModifiedCount() {
        return $this->modifiedCount;
    }

    public function getRenamedCount() {
        return $this->renamedCount;
    }

    public function getOtherStatusCount() {
        return $this->otherStatusCount;
    }




}