<?php

namespace VersionContol\GitControlBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * IssueMilestone
 *
 * @ORM\Table(name="issue_milestone", indexes={@ORM\Index(name="fk_issue_milestone_ver_user1_idx", columns={"ver_user_id"})})
 * @ORM\Entity
 */
class IssueMilestone
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
     * @var \VersionContol\GitControlBundle\Entity\VerUser
     *
     * @ORM\ManyToOne(targetEntity="VersionContol\GitControlBundle\Entity\VerUser")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ver_user_id", referencedColumnName="id")
     * })
     */
    private $verUser;



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
     * @param \VersionContol\GitControlBundle\Entity\VerUser $verUser
     *
     * @return IssueMilestone
     */
    public function setVerUser(\VersionContol\GitControlBundle\Entity\VerUser $verUser = null)
    {
        $this->verUser = $verUser;

        return $this;
    }

    /**
     * Get verUser
     *
     * @return \VersionContol\GitControlBundle\Entity\VerUser
     */
    public function getVerUser()
    {
        return $this->verUser;
    }
}
