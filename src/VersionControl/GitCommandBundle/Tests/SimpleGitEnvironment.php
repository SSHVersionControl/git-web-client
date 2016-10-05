<?php

/*
 * This file is part of the GitCommandBundle package.
 *
 * (c) Paul Schweppe <paulschweppe@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace VersionControl\GitCommandBundle\Tests;

use VersionControl\GitCommandBundle\GitCommands\GitEnvironmentInterface;

/**
 * Simple Git Environment use for testing. Implement to
 * store location of git path and ssh details   
 *
 * @author Paul Schweppe <paulschweppe@gmail.com>
 */
class SimpleGitEnvironment implements GitEnvironmentInterface {
    
    private $id;

    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $ssh;
    
    /**
     * @var string
     */
    private $host;
    
    /**
     * @var string
     */
    private $username;
    
    /**
     * @var string
     */
    private $password;
    
    
    /**
     * @var string
     */
    private $privateKey = null;
    
    /**
     * @var string
     */
    private $privateKeyPassword = null;
    

    public function __construct()
    {

    }
    
    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }

    
    /**
     * Set path
     *
     * @param string $path
     *
     * @return Project
     */
    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Get path
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }
    
    /**
     * Get SSH value
     * @return boolean
     */
    public function getSsh() {
        return $this->ssh;
    }
    
    /**
     * Set to use SSH
     * @param boolean $ssh
     */
    public function setSsh($ssh) {
        $this->ssh = $ssh;
    }

    /**
     * Get SSH host
     * @return string
     */
    public function getHost() {
        return $this->host;
    }
    
    /**
     * Set SSH host
     * @param string $host
     */
    public function setHost($host) {
        $this->host = $host;
    }

    /**
     * Get SSH username
     * @return string
     */
    public function getUsername() {
        return $this->username;
    }
    
    /**
     * set SSH username
     * @param type $username
     */
    public function setUsername($username) {
        $this->username = $username;
    }

    /**
     * Get SSH password
     * @return string
     */
    public function getPassword() {
        return $this->password;
    }

    /**
     * Set SSH password
     * @param string $password
     */
    public function setPassword($password) {
        if (!is_null($password)) {
            $this->password = $password;
        }
    }
    

    /**
     * Get Private Key
     * @return string
     */
    public function getPrivateKey() {
        return $this->privateKey;
    }

    /**
     * Get Private Key Password
     * @return string
     */
    public function getPrivateKeyPassword() {
        return $this->privateKeyPassword;
    }

    /**
     * Set Private Key
     * @param string $privateKey
     * @return \VersionControl\GitControlBundle\Entity\ProjectEnvironment
     */
    public function setPrivateKey($privateKey) {

        if (!is_null($privateKey)) {
            $this->privateKey = $privateKey;
        }
        return $this;
    }

    /**
     * Set Private Key Password
     * @param type $privateKeyPassword
     * @return \VersionControl\GitControlBundle\Entity\ProjectEnvironment
     */
    public function setPrivateKeyPassword($privateKeyPassword) {
        if (!is_null($privateKeyPassword)) {
            $this->privateKeyPassword = $privateKeyPassword;
        }
        return $this;
    }
    
    public function getPort() {
        return 22;
    }
    
}
