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
use VersionControl\GitControlBundle\Entity\ProjectEnvironment;

/**
 * This event is triggered whenever git update the files in the
 * working directory. eg Pull, checkout etc,.
 *
 * @author Paul Schweppe <paulschweppe@gmail.com>
 */
class GitAlterFilesEvent extends Event
{
    /**
     * @var ProjectEnvironment
     */
    private $gitEnvironment;

    /**
     * @var array
     */
    private $filesAltered;

    /**
     * @param GitEnvironmentInterface $gitEnvironment
     * @param array List of files altered
     */
    public function __construct(GitEnvironmentInterface $gitEnvironment, array $files)
    {
        $this->gitEnvironment = $gitEnvironment;
        $this->filesAltered = $files;
    }

    /**
     * @return GitEnvironmentInterface|ProjectEnvironment
     */
    public function getGitEnvironment()
    {
        return $this->gitEnvironment;
    }

    /**
     * @param GitEnvironmentInterface $gitEnvironment
     *
     * @return $this
     */
    public function setGitEnvironment(GitEnvironmentInterface $gitEnvironment)
    {
        $this->gitEnvironment = $gitEnvironment;

        return $this;
    }

    /**
     * @return array
     */
    public function getFilesAltered(): array
    {
        return $this->filesAltered;
    }

    /**
     * @param $filesAltered
     *
     * @return $this
     */
    public function setFilesAltered($filesAltered): self
    {
        $this->filesAltered = $filesAltered;

        return $this;
    }
}
