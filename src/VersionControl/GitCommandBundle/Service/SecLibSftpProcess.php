<?php

/*
 * This file is part of the GitCommandBundle package.
 *
 * (c) Paul Schweppe <paulschweppe@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace VersionControl\GitCommandBundle\Service;

use phpseclib\Net\SFTP;
use phpseclib\Crypt\RSA;
use VersionControl\GitCommandBundle\GitCommands\GitEnvironmentInterface;
use VersionControl\GitCommandBundle\GitCommands\Exception\SshLoginException;

/**
 * Use PhpSecLib to make SFTP requests. This is a wrapper for some of the functions
 * of PhpSecLib
 *
 * @link https://github.com/phpseclib/phpseclib
 * @author Paul Schweppe <paulschweppe@gmail.com>
 */
class SecLibSftpProcess implements SftpProcessInterface {
   
    /**
     * SFTP connection
     * @var phpseclib\Net\SFTP
     */
    protected $sftp = null;
    
    /**
     * The git environment with connection details
     * @var VersionControl\GitCommandBundle\GitCommands\GitEnvironmentInterface 
     */
    protected $gitEnvironment;

    
    public function setGitEnviroment(GitEnvironmentInterface $gitEnvironment){
        if(!$gitEnvironment->getSsh()){
            throw new \Exception('This Git Environment does not use SSH');
        }
        $this->gitEnvironment = $gitEnvironment;
    }
    
    /**
     * Connects to remote server 
     * 
     * @throws \InvalidArgumentException|\RuntimeException
     * @return void
     */
    protected function connect()
    {
        $host = $this->gitEnvironment->getHost();
        $username  = $this->gitEnvironment->getUsername();
        $port = $this->gitEnvironment->getPort();
        $password  = $this->gitEnvironment->getPassword();

        $privateKey = $this->gitEnvironment->getPrivateKey();
        $privateKeyPassword = $this->gitEnvironment->getPrivateKeyPassword();
                
        $this->sftp = new SFTP($host, 22);
        
        if(!$this->sftp){
            throw new SshLoginException(sprintf('SSH connection failed on "%s:%s"', $host, $port));
        }

        if (isset($username) && $privateKey != null) {
             $key = new RSA();

             //Set Private Key Password
             if($privateKeyPassword){
                 $key->setPassword($privateKeyPassword);
             }
             $key->loadKey($privateKey);

             //Login using private key
             if (!$this->sftp->login($username, $key)) { 
                 throw new SshLoginException(sprintf('SFTP authentication failed for user "%s" using private key', $username));
             }

        }else{
            if(!$this->sftp->login($username, $password)) {
                throw new SshLoginException(sprintf('SFTP authentication failed for user "%s" using password', $username));
            }
        }

    }
    
    /**
     * Gets the SFTP. Checks if connection already exists
     * @return phpseclib\Net\SFTP
     */
    protected function getSFTP(){
        if(!$this->sftp){
            $this->connect();
        }
        return $this->sftp;
    }
    
    /**
     * Gets all files in a directory
     * @param string $path
     * @return array
     */
    public function getDirectoryList($path){
        return $this->getSFTP()->rawlist($path);
    }
    
    /**
     * Check if file exists
     * @param string $filePath
     * @return boolean
     */
    public function fileExists($filePath){
        return $this->getSFTP()->file_exists($filePath);
    }
    
    /**
     * Gets files stats
     * @param string $filePath
     * @return array
     */
    public function getFileStats($filePath){
        return $this->getSFTP()->stat($filePath);
    }
    
    /**
     * Gets file contents
     * @param string $filePath
     * @return string
     */
    public function getFileContents($filePath){
        return $this->getSFTP()->get($filePath);
    }
    
    /**
     * Checks if file is a directory
     * @param string $filePath
     * @return boolean
     */
    public function isDir($filePath){
        return $this->getSFTP()->is_dir($filePath);
    }
    
    /**
     * Disconnect from SFTP
     */
    public function disconnect(){
        $this->sftp->disconnect();
        $this->sftp = null;
    }
    
}
