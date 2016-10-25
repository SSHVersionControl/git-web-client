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

interface IssueInterface
{
    /**
     * Get id.
     *
     * @return int
     */
    public function getId();

    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle();

    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription();

    /**
     * Get status.
     *
     * @return string
     */
    public function getStatus();

    /**
     * Get closedAt.
     *
     * @return \DateTime
     */
    public function getClosedAt();

    /**
     * Get createdAt.
     *
     * @return \DateTime
     */
    public function getCreatedAt();

    /**
     * Get updatedAt.
     *
     * @return \DateTime
     */
    public function getUpdatedAt();

    /**
     * Get issueMilestone.
     *
     * @return \VersionControl\GitControlBundle\Entity\IssueMilestone
     */
    public function getIssueMilestone();

    /**
     * Get User.
     *
     * @return \VersionControl\GitControlBundle\Entity\Issues\IssueUserInterface
     */
    public function getUser();

    /**
     * Get issueLabel.
     *
     * @return array of \VersionControl\GitControlBundle\Entity\Issue\IssueLabelInteface
     */
    public function getIssueLabel();

    /**
     * Get Issue Comments.
     *
     * @return array of \VersionControl\GitControlBundle\Entity\Issue\IssueCommentInteface
     */
    public function getIssueComments();

    /**
     * Set status.
     *
     * @param string $status
     *
     * @return Issue
     */
    public function setClosed();

    /**
     * Set status.
     *
     * @param string $status
     *
     * @return Issue
     */
    public function setOpen();

    public function isClosed();
}
