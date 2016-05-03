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


abstract class Issue
{
    /**
     * @var string
     *
     */
    private $title;

    /**
     * @var string
     *
     */
    private $description;

    /**
     * State of issue
     * @var string
     *
     */
    private $status;

    /**
     * @var \DateTime
     *
     */
    private $closedAt;

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
     * @var \VersionControl\GitControlBundle\Entity\IssueMilestone
     *
     */
    private $issueMilestone;

    /**
     * @var \VersionControl\GitControlBundle\Entity\Project
     *
     */
    private $project;

    /**
     * @var array
     *
     */
    private $issueLabel;
    
    /**
     * Issue comments
     * @var array
     * 
     */
    private $issueComments;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->issueLabel = array();
        $this->issueComments = array();
        $this->setCreatedAt(new \DateTime());
        $this->setStatus('open');
    }

    public function setId($id) {
        $this->id = $id;
        return $this;
    }

        /**
     * Set title
     *
     * @param string $title
     *
     * @return Issue
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set description
     *
     * @param string $description
     *
     * @return Issue
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set status
     *
     * @param string $status
     *
     * @return Issue
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set closedAt
     *
     * @param \DateTime $closedAt
     *
     * @return Issue
     */
    public function setClosedAt($closedAt)
    {
        $this->closedAt = $closedAt;

        return $this;
    }

    /**
     * Get closedAt
     *
     * @return \DateTime
     */
    public function getClosedAt()
    {
        return $this->closedAt;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Issue
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
     * @return Issue
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
     * Set githubNumber
     *
     * @param integer $githubNumber
     *
     * @return Issue
     */
    public function setGithubNumber($githubNumber)
    {
        $this->githubNumber = $githubNumber;

        return $this;
    }

    /**
     * Get githubNumber
     *
     * @return integer
     */
    public function getGithubNumber()
    {
        return $this->githubNumber;
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
     * Set issueMilestone
     *
     * @param \VersionControl\GitControlBundle\Entity\IssueMilestone $issueMilestone
     *
     * @return Issue
     */
    public function setIssueMilestone(\VersionControl\GitControlBundle\IssueMilestone $issueMilestone = null)
    {
        $this->issueMilestone = $issueMilestone;

        return $this;
    }

    /**
     * Get issueMilestone
     *
     * @return \VersionControl\GitControlBundle\Entity\IssueMilestone
     */
    public function getIssueMilestone()
    {
        return $this->issueMilestone;
    }

    /**
     * Set project
     *
     * @param \VersionControl\GitControlBundle\Entity\Project $project
     *
     * @return Issue
     */
    public function setProject(\VersionControl\GitControlBundle\Entity\Project $project = null)
    {
        $this->project = $project;

        return $this;
    }

    /**
     * Get project
     *
     * @return \VersionControl\GitControlBundle\Entity\Project
     */
    public function getProject()
    {
        return $this->project;
    }


    /**
     * Get User
     *
     * @return \VersionControl\GitControlBundle\Entity\Issues\IssueUserInterface
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Add issueLabel
     *
     * @param \VersionControl\GitControlBundle\Entity\Issues\IssueLabel $issueLabel
     *
     * @return Issue
     */
    public function addIssueLabel(\VersionControl\GitControlBundle\Entity\Issues\IssueLabel $issueLabel)
    {
        $this->issueLabel[] = $issueLabel;

        return $this;
    }

    /**
     * Remove issueLabel
     *
     * @param \VersionControl\GitControlBundle\Entity\Issues\IssueLabel $issueLabel
     */
    public function removeIssueLabel(\VersionControl\GitControlBundle\Entity\Issues\IssueLabel $issueLabel)
    {
        $this->issueLabel->removeElement($issueLabel);
    }

    /**
     * Get issueLabel
     *
     * @return array
     */
    public function getIssueLabel()
    {
        return $this->issueLabel;
    }
    
    /**
     * Get Issue Comments
     * @return array of \VersionControl\GitControlBundle\Entity\Issue\IssueCommentInteface
     */
    public function getIssueComments() {
        return $this->issueComments;
    }

    /**
     * Set Issue Comments
     * @param array $issueComments
     * @return \VersionControl\GitControlBundle\Entity\Issue
     */
    public function setIssueComments(array $issueComments) {
        $this->issueComments = $issueComments;
        return $this;
    }

    /**
     * Set status
     *
     * @param string $status
     *
     * @return Issue
     */
    public function setClosed()
    {
        $this->status = 'closed';
        return $this;
    }
    
    /**
     * Set status
     *
     * @param string $status
     *
     * @return Issue
     */
    public function setOpen()
    {
        $this->status = 'open';
        return $this;
    }
    
    public function isClosed(){
        return ($this->status === 'closed')?true:false;
    }

}


