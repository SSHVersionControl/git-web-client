<?php

namespace VersionContol\GitControlBundle\Entity\Issues;

use Doctrine\ORM\Mapping as ORM;

/**
 * IssueComment
 *
 */
interface IssueCommentInteface
{
    
    /**
     * Set comment
     *
     * @param string $comment
     *
     * @return IssueComment
     */
    public function setComment($comment);

    /**
     * Get comment
     *
     * @return string
     */
    public function getComment();

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return IssueComment
     */
    public function setCreatedAt($createdAt);

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt();

    /**
     * Set updatedAt
     *
     * @param \DateTime $updatedAt
     *
     * @return IssueComment
     */
    public function setUpdatedAt($updatedAt);

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt();

    /**
     * Get id
     *
     * @return integer
     */
    public function getId();

      
    /**
     * Gets issue
     * @return \VersionContol\GitControlBundle\Entity\Issues\IssueInterface
     */
    public function getIssue();
    
    /**
     * Gets Createor of comment
     * @return \VersionContol\GitControlBundle\Entity\Issues\IssueUserInterface
     */
    public function getUser();

}
