<?php
/*
 * This file is part of the GitControlBundle package.
 *
 * (c) Paul Schweppe <paulschweppe@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace VersionControl\GitControlBundle\Entity\Issues;

/**
 * IssueComment.
 */
interface IssueCommentInteface
{
    /**
     * Set comment.
     *
     * @param string $comment
     *
     * @return IssueComment
     */
    public function setComment($comment);

    /**
     * Get comment.
     *
     * @return string
     */
    public function getComment();

    /**
     * Set createdAt.
     *
     * @param \DateTime $createdAt
     *
     * @return IssueComment
     */
    public function setCreatedAt($createdAt);

    /**
     * Get createdAt.
     *
     * @return \DateTime
     */
    public function getCreatedAt();

    /**
     * Set updatedAt.
     *
     * @param \DateTime $updatedAt
     *
     * @return IssueComment
     */
    public function setUpdatedAt($updatedAt);

    /**
     * Get updatedAt.
     *
     * @return \DateTime
     */
    public function getUpdatedAt();

    /**
     * Get id.
     *
     * @return int
     */
    public function getId();

    /**
     * Gets issue.
     *
     * @return \VersionControl\GitControlBundle\Entity\Issues\IssueInterface
     */
    public function getIssue();

    /**
     * Gets Createor of comment.
     *
     * @return \VersionControl\GitControlBundle\Entity\Issues\IssueUserInterface
     */
    public function getUser();
}
