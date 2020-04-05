<?php
// src/VersionControl/GitCommandBundle/Entity/GitCommitFile.php

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
 * Entity containing the file name and status
 * for a commit.
 *
 * @author Paul Schweppe <paulschweppe@gmail.com>
 */
class GitCommitFile
{
    /**
     * The file Path.
     *
     * @var string
     */
    protected $filePath;

    /*
     * Single character repersenting the files status in commit;
     * Possible values are: A|C|D|M|R|T|U|X|B
     *
     * Added (A),
     * Copied (C),
     * Deleted (D),
     * Modified (M),
     * Renamed (R),
     * have their type (i.e. regular file, symlink, submodule, …​)
     * changed (T),
     * Unmerged (U),
     * Unknown (X),
     * Broken (B)
     *
     * @var string
     */
    protected $statusChange;

    /**
     * The Original line.
     *
     * @var string
     */
    protected $line;

    /**
     * Excepts a line in the format:
     * Status       File name
     * M       typo3conf/ext/fr_mapfeusertoasti/log/log.txt.
     *
     * @param $line
     */
    public function __construct($line)
    {
        $this->line = $line;

        $this->statusChange = $line[0];

        $this->filePath = trim(substr($line, 1));
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }

    public function getStatusChange()
    {
        return $this->statusChange;
    }

    public function getLine(): string
    {
        return $this->line;
    }

    public function setFilePath($filePath)
    {
        $this->filePath = $filePath;

        return $this;
    }

    public function setStatusChange($statusChange)
    {
        $this->statusChange = $statusChange;

        return $this;
    }

    public function setLine($line)
    {
        $this->line = $line;

        return $this;
    }
}
