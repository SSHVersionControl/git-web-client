<?php

namespace VersionControl\GitControlBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use VersionControl\GitControlBundle\Entity\Issues\IssueInterface;
/**
 * Issue
 *
 * @ORM\Table(name="issue", indexes={@ORM\Index(name="fk_issue_ver_user1_idx", columns={"ver_user_id"}), @ORM\Index(name="fk_issue_project1_idx", columns={"project_id"}), @ORM\Index(name="fk_issue_issue_milestone1_idx", columns={"issue_milestone_id"})})
 * @ORM\Entity(repositoryClass="VersionControl\GitControlBundle\Repository\IssueRepository")
 * @ORM\HasLifecycleCallbacks
 */
class Issue implements IssueInterface
{
    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=true)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string", length=45, nullable=true)
     */
    private $status;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="closed_at", type="datetime", nullable=true)
     */
    private $closedAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @var integer
     *
     * @ORM\Column(name="github_number", type="integer", nullable=true)
     */
    private $githubNumber;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \VersionControl\GitControlBundle\Entity\IssueMilestone
     *
     * @ORM\ManyToOne(targetEntity="VersionControl\GitControlBundle\Entity\IssueMilestone")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="issue_milestone_id", referencedColumnName="id")
     * })
     */
    private $issueMilestone;

    /**
     * @var \VersionControl\GitControlBundle\Entity\Project
     *
     * @ORM\ManyToOne(targetEntity="VersionControl\GitControlBundle\Entity\Project")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="project_id", referencedColumnName="id")
     * })
     */
    private $project;

    /**
     * @var \VersionControl\GitControlBundle\Entity\User\User
     *
     * @ORM\ManyToOne(targetEntity="VersionControl\GitControlBundle\Entity\User\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ver_user_id", referencedColumnName="id")
     * })
     */
    private $verUser;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="VersionControl\GitControlBundle\Entity\IssueLabel", inversedBy="issue")
     * @ORM\JoinTable(name="issue_has_issue_label",
     *   joinColumns={
     *     @ORM\JoinColumn(name="issue_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="issue_label_id", referencedColumnName="id")
     *   }
     * )
     */
    private $issueLabel;
    
    /**
     * Issue comments
     * @var \Doctrine\Common\Collections\Collection
     * 
     * @ORM\OneToMany(targetEntity="VersionControl\GitControlBundle\Entity\IssueComment", mappedBy="issue", fetch="EXTRA_LAZY") 
     */
    private $issueComments;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->issueLabel = new \Doctrine\Common\Collections\ArrayCollection();
        $this->issueComments = new \Doctrine\Common\Collections\ArrayCollection();
        $this->setCreatedAt(new \DateTime());
        $this->setStatus('open');
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
    public function setIssueMilestone(\VersionControl\GitControlBundle\Entity\Issues\IssueMilestone $issueMilestone = null)
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
     * Set verUser
     *
     * @param \VersionControl\GitControlBundle\Entity\User\User $verUser
     *
     * @return Issue
     */
    public function setVerUser(\VersionControl\GitControlBundle\Entity\User\User $verUser = null)
    {
        $this->verUser = $verUser;

        return $this;
    }
    
    /**
     * Get User
     *
     * @return \VersionControl\GitControlBundle\Entity\Issues\IssueUserInterface
     */
    public function getUser()
    {
        return $this->verUser;
    }

    /**
     * Get verUser
     *
     * @return \VersionControl\GitControlBundle\Entity\User\User
     */
    public function getVerUser()
    {
        return $this->verUser;
    }

    /**
     * Add issueLabel
     *
     * @param \VersionControl\GitControlBundle\Entity\IssueLabel $issueLabel
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
     * @param \VersionControl\GitControlBundle\Entity\IssueLabel $issueLabel
     */
    public function removeIssueLabel(\VersionControl\GitControlBundle\Entity\Issues\IssueLabel $issueLabel)
    {
        $this->issueLabel->removeElement($issueLabel);
    }

    /**
     * Get issueLabel
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getIssueLabel()
    {
        return $this->issueLabel;
    }
    
    /**
     * Get Issue Comments
     * @return \Doctrine\Common\Collections\Collection of \VersionControl\GitControlBundle\Entity\IssueComment
     */
    public function getIssueComments() {
        return $this->issueComments;
    }

    /**
     * Set Issue Comments
     * @param \Doctrine\Common\Collections\Collection $issueComments
     * @return \VersionControl\GitControlBundle\Entity\Issue
     */
    public function setIssueComments(array $issueComments) {
        $this->issueComments = $issueComments;
        return $this;
    }

        
    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function updateModifiedDatetime() {
        // update the modified time
        //$this->setCreatedAt(new \DateTime());
        $this->setUpdatedAt(new \DateTime());
        if($this->getStatus() === 'closed'){
            $this->setClosedAt(new \DateTime());
        }
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
