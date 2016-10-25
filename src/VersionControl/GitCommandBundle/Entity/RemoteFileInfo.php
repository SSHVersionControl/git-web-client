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
 * Remote File Info.
 *
 * @author Paul Schweppe <paulschweppe@gmail.com>
 */
class RemoteFileInfo
{
    /**
     * Absolute path to file.
     *
     * @var string
     */
    protected $fullPath;

    /**
     * File Extension.
     *
     * @var string
     */
    protected $extension;

    /**
     * File name without any path information.
     *
     * @var string
     */
    protected $filename;

    /**
     * Path without the filename.
     *
     * @var string
     */
    protected $path;

    /**
     * File permissions.
     *
     * @var string
     */
    protected $perms;

    /**
     * Filesize in bytes.
     *
     * @var int
     */
    protected $size;

    /**
     * @var type
     */
    protected $uid;
    protected $gid;
    protected $mode;
    protected $aTime;
    protected $mTime;
    protected $type;
    protected $fileTypes;

    protected $gitPath;

    /**
     * Git log Entity.
     *
     * @var GitLog
     */
    protected $gitLog;

    public function __construct($fileData)
    {
        foreach ($fileData as $key => $value) {
            $setMethod = 'set'.ucfirst($key);
            if (method_exists($this, $setMethod)) {
                call_user_func_array(array($this, $setMethod), array($value));
            }
        }

        $this->fileTypes = array(
            1 => 'NET_SFTP_TYPE_REGULAR',
            2 => 'NET_SFTP_TYPE_DIRECTORY',
            3 => 'NET_SFTP_TYPE_SYMLINK',
            4 => 'NET_SFTP_TYPE_SPECIAL',
            5 => 'NET_SFTP_TYPE_UNKNOWN',
            // the followin types were first defined for use in SFTPv5+
            // http://tools.ietf.org/html/draft-ietf-secsh-filexfer-05#section-5.2
            6 => 'NET_SFTP_TYPE_SOCKET',
            7 => 'NET_SFTP_TYPE_CHAR_DEVICE',
            8 => 'NET_SFTP_TYPE_BLOCK_DEVICE',
            9 => 'NET_SFTP_TYPE_FIFO',
        );
    }

    public function getFullPath()
    {
        return $this->fullPath;
    }

    public function getExtension()
    {
        return $this->extension;
    }

    public function getFilename()
    {
        return $this->filename;
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getPerms()
    {
        return $this->perms;
    }

    public function getSize()
    {
        return $this->size;
    }

    public function getUid()
    {
        return $this->uid;
    }

    public function getGid()
    {
        return $this->gid;
    }

    public function getMode()
    {
        return $this->mode;
    }

    public function getATime()
    {
        return $this->aTime;
    }

    public function getMTime()
    {
        return $this->mTime;
    }

    public function getType()
    {
        return $this->type;
    }

    public function setFullPath($fullPath)
    {
        $this->fullPath = $fullPath;

        return $this;
    }

    public function setExtension($extension)
    {
        $this->extension = $extension;

        return $this;
    }

    public function setFilename($filename)
    {
        $this->filename = $filename;
        $pathParts = pathinfo($filename);
        if (key_exists('extension', $pathParts)) {
            $this->setExtension($pathParts['extension']);
        }

        return $this;
    }

    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    public function setPerms($perms)
    {
        $this->perms = $perms;

        return $this;
    }

    public function setPermissions($perms)
    {
        return $this->setPerms($perms);
    }

    public function setSize($size)
    {
        $this->size = $size;

        return $this;
    }

    public function setUid($uid)
    {
        $this->uid = $uid;

        return $this;
    }

    public function setGid($gid)
    {
        $this->gid = $gid;

        return $this;
    }

    public function setMode($mode)
    {
        $this->mode = $mode;

        return $this;
    }

    public function setATime($aTime)
    {
        $this->aTime = $aTime;

        return $this;
    }

    public function setMTime($mTime)
    {
        $this->mTime = $mTime;

        return $this;
    }

    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    public function getGitPath()
    {
        return $this->gitPath;
    }

    public function setGitPath($gitPath)
    {
        $this->gitPath = $gitPath;

        return $this;
    }

    /**
     * @return type
     */
    public function getGitLog()
    {
        return $this->gitLog;
    }

    public function setGitLog(GitLog $gitLog)
    {
        $this->gitLog = $gitLog;

        return $this;
    }

    public function isDir()
    {
        return $this->type === 2 ? true : false;
    }

    public function isFile()
    {
        return $this->type === 1 ? true : false;
    }

    public function isLink()
    {
        return $this->type === 3 ? true : false;
    }
}
