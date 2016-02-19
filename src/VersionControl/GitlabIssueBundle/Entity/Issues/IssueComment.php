<?php

namespace VersionControl\GitlabIssueBundle\Entity\Issues;

use VersionContol\GitControlBundle\Entity\Issues\IssueCommentInteface;

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
     * @var \VersionControl\GitlabIssueBundle\Entity\Issues\Issue
     *
     */
    private $issue;

    /**
     * Gitlab User
     * @var VersionControl\GitlabIssueBundle\Entity\User 
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
     * @param \VersionContol\GitControlBundle\Entity\Issue $issue
     * @return \VersionContol\GitControlBundle\Entity\IssueComment
     */
    public function setIssue(\VersionControl\GitlabIssueBundle\Entity\Issues\Issue $issue) {
        $this->issue = $issue;
        return $this;
    }
    
    /**
     * Gets issue
     * @return \VersionContol\GitControlBundle\Entity\Issue
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
