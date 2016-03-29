<?php

namespace VersionControl\GitControlBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use VersionControl\GitControlBundle\Entity\Issues\IssueMilestoneInterface;

/**
 * IssueMilestone
 *
 * @ORM\Table(name="issue_milestone", indexes={@ORM\Index(name="fk_issue_milestone_ver_user1_idx", columns={"ver_user_id"})})
 * @ORM\Entity(repositoryClass="VersionControl\GitControlBundle\Repository\IssueMilestoneRepository")
 * @ORM\HasLifecycleCallbacks
 * 
 */
class IssueMilestone implements IssueMilestoneInterface
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
     * @ORM\Column(name="state", type="string", length=45, nullable=true)
     */
    private $state;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="due_on", type="datetime", nullable=true)
     */
    private $dueOn;

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
     * @var \DateTime
     *
     * @ORM\Column(name="closed_at", type="datetime", nullable=true)
     */
    private $closedAt;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

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
     * @var \VersionControl\GitControlBundle\Entity\Project
     *
     * @ORM\ManyToOne(targetEntity="VersionControl\GitControlBundle\Entity\Project")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="project_id", referencedColumnName="id")
     * })
     */
    private $project;


    /**
     * Constructor
     */
    public function __construct()
    {
        $this->setCreatedAt(new \DateTime());
        $this->setState('open');
    }
    

    /**
     * Set title
     *
     * @param string $title
     *
     * @return IssueMilestone
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
     * @return IssueMilestone
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
     * Set state
     *
     * @param string $state
     *
     * @return IssueMilestone
     */
    public function setState($state)
    {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state
     *
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Set dueOn
     *
     * @param \DateTime $dueOn
     *
     * @return IssueMilestone
     */
    public function setDueOn($dueOn)
    {
        $this->dueOn = $dueOn;

        return $this;
    }

    /**
     * Get dueOn
     *
     * @return \DateTime
     */
    public function getDueOn()
    {
        return $this->dueOn;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return IssueMilestone
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
     * @return IssueMilestone
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
     * Set closedAt
     *
     * @param \DateTime $closedAt
     *
     * @return IssueMilestone
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
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set verUser
     *
     * @param \VersionControl\GitControlBundle\Entity\User\User $verUser
     *
     * @return IssueMilestone
     */
    public function setVerUser(\VersionControl\GitControlBundle\Entity\User\User $verUser = null)
    {
        $this->verUser = $verUser;

        return $this;
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
     * 
     * @return \VersionControl\GitControlBundle\Entity\User\User
     */
    public function getUser(){
        return $this->verUser;
    }
    
    /**
     * Set project
     *
     * @param \VersionControl\GitControlBundle\Entity\Project $project
     *
     * @return IssueMilestone
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
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function updateModifiedDatetime() {
        // update the modified time
        //$this->setCreatedAt(new \DateTime());
        $this->setUpdatedAt(new \DateTime());
        if($this->getState() === 'closed'){
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
        $this->state = 'closed';

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
        $this->state = 'open';

        return $this;
    }
    
    public function isClosed(){
        return ($this->state === 'closed')?true:false;
    }
}
