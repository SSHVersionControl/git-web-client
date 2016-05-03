<?php
/*
 * This file is part of the GithubIssueBundle package.
 *
 * (c) Paul Schweppe <paulschweppe@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace VersionControl\GithubIssueBundle\Entity\Issues;

use VersionControl\GitControlBundle\Entity\Issues\IssueCommentInteface;

/**
 * IssueComment
 *
 */
class IssueComment implements IssueCommentInteface
{
    /**
     * @var string
     *
     */
    private $comment;

    /**
     * @var \DateTime
     *
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     */
    private $updatedAt;

    /**
     * @var integer
     *
     */
    private $id;

    
    /**
     * @var \VersionControl\GithubIssueBundle\Entity\Issues\Issue
     *
     */
    private $issue;

    /**
     * Github User
     * @var VersionControl\GithubIssueBundle\Entity\User 
     */
    private $user;


    public function __construct() {
        $this->setCreatedAt(new \DateTime());
    }

    /**
     * Set comment
     *
     * @param string $comment
     *
     * @return IssueComment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return IssueComment
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return IssueComment
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function setId($id)
    {
        $this->id = $id;
    }
    
    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }


    /**
     * Sets issue
     * @param \VersionControl\GitControlBundle\Entity\Issue $issue
     * @return \VersionControl\GitControlBundle\Entity\IssueComment
     */
    public function setIssue(\VersionControl\GithubIssueBundle\Entity\Issues\Issue $issue) {
        $this->issue = $issue;
        return $this;
    }
    
    /**
     * Gets issue
     * @return \VersionControl\GitControlBundle\Entity\Issue
     */
    public function getIssue() {
        return $this->issue;
    }
    
    public function setUser($user) {
        $this->user = $user;
    }
    
    /**
     * 
     * @return type
     */
    public function getUser() {
        return $this->user;
    }
    


}
