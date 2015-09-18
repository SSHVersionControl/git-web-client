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
class FileInfo {
    
    /**
     * File name
     * @var string 
     */
    protected $name;
    
   /**
     * File path related to git directory
     * @var string 
     */
    protected $path;


    /**
     * File full path to file
     * @var string 
     */
    protected $fullPath;
    
    /**
     * File type 
     * Possible values are fifo, char, dir, block, link, file, socket and unknown.
     * @var string 
     */
    protected $type;
    
    /**
     * Extenstion type
     * @var string 
     */
    protected $extension;
    
    /**
     * Git log Entity
     * @var GitLog 
     */
    protected $gitLog;
    
    public function __construct($name) {
        $this->setName($name);
    }
    
    public function getName() {
        return $this->name;
    }

    public function getPath() {
        return $this->path;
    }

    public function getType() {
        return $this->type;
    }

    public function getGitLog() {
        return $this->gitLog;
    }

    public function setName($name) {
        $this->name = $name;
        return $this;
    }

    public function setPath($path) {
        $this->path = $path;
        return $this;
    }

    public function setType($type) {
        $this->type = $type;
        return $this;
    }

    public function setGitLog(GitLog $gitLog) {
        $this->gitLog = $gitLog;
        return $this;
    }

    public function getExtension() {
        return $this->extension;
    }

    public function setExtension($extension) {
        $this->extension = $extension;
        return $this;
    }
    
    public function getFullPath() {
        return $this->fullPath;
    }

    public function setFullPath($fullPath) {
        $this->fullPath = $fullPath;
        return $this;
    }





    
    
    
}
