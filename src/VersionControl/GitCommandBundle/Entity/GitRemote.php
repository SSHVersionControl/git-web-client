<?php
// src/VersionControl/GitCommandBundle/Entity/GitRemote.php

namespace VersionControl\GitCommandBundle\Entity;


/**
 * Git Remote object
 *
 * @author fr_user
 */
class GitRemote {
    
    protected $shortName;
    
    protected $longName;
    
    protected $state;
    
    public function getShortName() {
        return $this->shortName;
    }

    public function getLongName() {
        return $this->longName;
    }

    public function getState() {
        return $this->state;
    }

    public function setShortName($shortName) {
        $this->shortName = $shortName;
        return $this;
    }

    public function setLongName($longName) {
        $this->longName = $longName;
        return $this;
    }

    public function setState($state) {
        $this->state = $state;
        return $this;
    }


}