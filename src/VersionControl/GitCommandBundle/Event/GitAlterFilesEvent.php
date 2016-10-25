<?php
/*
 * This file is part of the GitCommandBundle package.
 *
 * (c) Paul Schweppe <paulschweppe@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace VersionControl\GitCommandBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use VersionControl\GitCommandBundle\GitCommands\GitEnvironmentInterface;

/**
 * This event is triggered whenever git update the files in the
 * working directory. eg Pull, checkout etc,.
 *
 * @author Paul Schweppe <paulschweppe@gmail.com>
 */
class GitAlterFilesEvent extends Event
{
    /**
     * @var VersionControl\GitCommandBundle\Entity\ProjectEnvironment
     */
    private $gitEnvironment;

    private $filesAltered = array();

    /**
     * @param GitEnvironmentInterface $gitEnvironment
     * @param List of files altered
     */
    public function __construct(GitEnvironmentInterface $gitEnvironment, $files)
    {
        $this->gitEnvironment = $gitEnvironment;
        $this->filesAltered = $files;
    }

    public function getGitEnvironment()
    {
        return $this->gitEnvironment;
    }

    public function setGitEnvironment(GitEnvironmentInterface $gitEnvironment)
    {
        $this->gitEnvironment = $gitEnvironment;

        return $this;
    }

    public function getFilesAltered()
    {
        return $this->filesAltered;
    }

    public function setFilesAltered($filesAltered)
    {
        $this->filesAltered = $filesAltered;

        return $this;
    }
}
