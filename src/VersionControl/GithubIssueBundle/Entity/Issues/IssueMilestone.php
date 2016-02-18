<?php
namespace VersionControl\GithubIssueBundle\Entity\Issues;

use VersionContol\GitControlBundle\Entity\Issues\IssueMilestoneInterface;

/**
 * IssueMilestone
 *
 * 
 */
class IssueMilestone implements IssueMilestoneInterface
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
     *
     * @var \VersionControl\GithubIssueBundle\Entity\User
     */
    private $user;
    

    /**
     * 
     * @param integer $id
     */
    public function setId($id){
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
     * Get verUser
     *
     * @return \VersionControl\GithubIssueBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }
    
    /**
     * Get verUser
     *
     */
    public function setUser(\VersionControl\GithubIssueBundle\Entity\User $user)
    {
        $this->user = $user;
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
