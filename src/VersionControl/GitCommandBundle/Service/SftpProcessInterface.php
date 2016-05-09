<?php
/*
 * This file is part of the Version Control package.
 * 
 * (c) Paul Schweppe <paulschweppe@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace VersionControl\GitCommandBundle\Service;

use VersionControl\GitCommandBundle\GitCommands\GitEnvironmentInterface;

/**
 * Interface for SFTP Process
 *
 * @author Paul Schweppe <paulschweppe@gmail.com>
 */
interface SftpProcessInterface {
    
    /**
     * Set Git Environment
     * @param GitEnvironmentInterface $gitEnvironment
     */
    public function setGitEnviroment(GitEnvironmentInterface $gitEnvironment);
    
    /**
     * Gets all files in a directory
     * @param string $path
     * @return array
     */
    public function getDirectoryList($path);
    
    /**
     * Check if file exists
     * @param string $filePath
     * @return boolean
     */
    public function fileExists($filePath);
    
    /**
     * Gets files stats
     * @param string $filePath
     * @return array
     */
    public function getFileStats($filePath);
    
    /**
     * Gets file contents
     * @param string $filePath
     * @return string
     */
    public function getFileContents($filePath);
    
    /**
     * Checks if file is a directory
     * @param string $filePath
     * @return boolean
     */
    public function isDir($filePath);
    
    /**
     * Disconnect from SFTP
     */
    public function disconnect();
}
