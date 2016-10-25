<?php
// src/VersionControl/GitControlBundle/Entity/GitDiff.php
/*
 * This file is part of the GitControlBundle package.
 *
 * (c) Paul Schweppe <paulschweppe@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace VersionControl\GitControlBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use VersionControl\GitControlBundle\Validator\Constraints as VersionAssert;

/**
 * A commit entity used to create a from with validation.
 *
 * @author paul schweppe
 * @VersionAssert\StatusHash
 */
class Commit
{
    /**
     * @var string
     * @Assert\NotBlank
     */
    private $comment;

    /**
     * @var string
     * @Assert\NotBlank
     */
    private $statusHash;

    /**
     * @var array
     * @Assert\NotBlank
     */
    private $files;

    /**
     * @var Project
     */
    private $project;

    /**
     * @var int
     */
    private $issue;

    /**
     * @var int
     */
    private $issueAction;

    /**
     * Flag to state if the system should push after commit.
     *
     * @var bool
     */
    private $pushOnCommit;

    /**
     * @var array
     */
    private $pushRemote;

    public function __construct()
    {
        $this->pushRemote = array();
    }

    public function getComment()
    {
        return $this->comment;
    }

    public function getStatusHash()
    {
        return $this->statusHash;
    }

    public function getFiles()
    {
        return $this->files;
    }

    public function getProject()
    {
        return $this->project;
    }

    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    public function setStatusHash($statusHash)
    {
        $this->statusHash = $statusHash;

        return $this;
    }

    public function setFiles($files)
    {
        $this->files = $files;

        return $this;
    }

    public function setProject(Project $project)
    {
        $this->project = $project;

        return $this;
    }

    public function getIssue()
    {
        return $this->issue;
    }

    public function getIssueAction()
    {
        return $this->issueAction;
    }

    public function setIssue($issue)
    {
        $this->issue = $issue;

        return $this;
    }

    public function setIssueAction($issueAction)
    {
        $this->issueAction = $issueAction;

        return $this;
    }

    public function getPushOnCommit()
    {
        return $this->pushOnCommit;
    }

    public function setPushOnCommit($pushOnCommit)
    {
        $this->pushOnCommit = $pushOnCommit;

        return $this;
    }

    public function getPushRemote()
    {
        return $this->pushRemote;
    }

    public function setPushRemote($pushRemote)
    {
        $this->pushRemote = $pushRemote;

        return $this;
    }
}
