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
 *
 * @author Paul Schweppe <paulschweppe@gmail.com>
 */
interface FileInfoInterface{
    
    /*
     * Get Functions
     */
    public function getExtension();
    public function getFilename();
    public function getPath();
    public function getPerms();
    public function getSize();
    public function getATime();
    public function getMTime();
    public function getType();
    
    public function getFullPath();
    public function getGitPath();
    public function getGitLog();
    
    /*
     * Set Functions
     */
    public function setGitPath($gitPath);
    public function setGitLog(GitLog $gitLog);
    
    public function isDir();
    public function isFile();
    public function isLink();
    
}
