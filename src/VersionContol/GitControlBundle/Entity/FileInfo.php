<?php
// src/VersionContol/GitControlBundle/Entity/FileInfo.php

namespace VersionContol\GitControlBundle\Entity;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of FileInfo
 *
 * @author paul
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
