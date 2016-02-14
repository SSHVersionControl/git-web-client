<?php

namespace VersionContol\GitControlBundle\Entity\Issues;

use Doctrine\ORM\Mapping as ORM;

/**
 * IssueMilestone
 *
 * 
 */
class IssueMilestone
{
    /**
     * @var string
     *
     */
    private $title;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     *
     */
    private $state;

    /**
     * @var \DateTime
     *
     */
    private $dueOn;

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
     * @var \DateTime
     *
     */
    private $closedAt;

    /**
     * @var integer
     *
     */
    private $id;

    
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
     * @param \VersionContol\GitControlBundle\Entity\User\User $verUser
     *
     * @return IssueMilestone
     */
    public function setVerUser(\VersionContol\GitControlBundle\Entity\User\User $verUser = null)
    {
        $this->verUser = $verUser;

        return $this;
    }

    /**
     * Get verUser
     *
     * @return \VersionContol\GitControlBundle\Entity\User\User
     */
    public function getVerUser()
    {
        return $this->verUser;
    }
    
    /**
     * Set project
     *
     * @param \VersionContol\GitControlBundle\Entity\Project $project
     *
     * @return IssueMilestone
     */
    public function setProject(\VersionContol\GitControlBundle\Entity\Project $project = null)
    {
        $this->project = $project;

        return $this;
    }

    /**
     * Get project
     *
     * @return \VersionContol\GitControlBundle\Entity\Project
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
