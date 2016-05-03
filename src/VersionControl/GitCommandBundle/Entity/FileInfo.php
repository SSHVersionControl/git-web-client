<?php
// src/VersionControl/GitCommandBundle/Entity/FileInfo.php

/*
 * This file is part of the GitCommandBundle package.
 *
 * (c) Paul Schweppe <paulschweppe@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace VersionControl\GitCommandBundle\Entity;

/**
 * Info on Local file including git log and path. 
 *
 * @link http://php.net/manual/en/class.splfileinfo.php
 * @author Paul Schweppe <paulschweppe@gmail.com>
 */
class FileInfo extends \SplFileInfo{
    
   
    /**
     * File full path to file
     * @var string 
     */
    protected $gitPath;
    
    /**
     * Git log Entity
     * @var GitLog 
     */
    protected $gitLog;


    public function getGitLog() {
        return $this->gitLog;
    }

    public function setGitLog(GitLog $gitLog) {
        $this->gitLog = $gitLog;
        return $this;
    }  
    
    public function getFullPath() {
        return $this->getRealPath();
    }
        
    public function getGitPath() {
        return $this->gitPath;
    }

    public function setGitPath($gitPath) {
        $this->gitPath = $gitPath;
        return $this;
    }
    
}
