<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace VersionControl\GitCommandBundle\GitCommands;

/**
 * Description of GitEnvironmentInterface
 *
 * @author fr_user
 */
interface GitEnvironmentInterface {
    
    /**
     * Get Path to git folder
     */
    public function getPath();

   
    /**
     * Get SSH value
     * @return boolean
     */
    public function getSsh();
    

    /**
     * Get SSH host
     * @return string
     */
    public function getHost();
    

    /**
     * Get SSH username
     * @return string
     */
    public function getUsername();
    

    /**
     * Get SSH password
     * @return string
     */
    public function getPassword();
    
}
