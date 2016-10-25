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
class FileInfo extends \SplFileInfo
{
    /**
     * File full path to file.
     *
     * @var string
     */
    protected $gitPath;

    /**
     * Git log Entity.
     *
     * @var GitLog
     */
    protected $gitLog;

    /**
     * Gets the git log for the file.
     *
     * @return \VersionControl\GitCommandBundle\Entity\GitLog
     */
    public function getGitLog()
    {
        return $this->gitLog;
    }

    /**
     * Sets the git log for the file.
     *
     * @param \VersionControl\GitCommandBundle\Entity\GitLog $gitLog
     *
     * @return \VersionControl\GitCommandBundle\Entity\FileInfo
     */
    public function setGitLog(GitLog $gitLog)
    {
        $this->gitLog = $gitLog;

        return $this;
    }

    /**
     * Gets absolute path to file. Wrapper for SplFileInfo::getRealPath.
     *
     * @link http://php.net/manual/en/splfileinfo.getrealpath.php
     *
     * @return string
     */
    public function getFullPath()
    {
        return $this->getRealPath();
    }

    /**
     * Gets the file path relative to the .git folder.
     *
     * @return string
     */
    public function getGitPath()
    {
        return $this->gitPath;
    }

    /**
     * Sets the file path relative to the .git folder.
     *
     * @param string $gitPath
     *
     * @return \VersionControl\GitCommandBundle\Entity\FileInfo
     */
    public function setGitPath($gitPath)
    {
        $this->gitPath = $gitPath;

        return $this;
    }
}
