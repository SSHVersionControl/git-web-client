<?php

namespace VersionContol\GitControlBundle\Entity;

use VersionContol\GitControlBundle\Entity\Issues\IssueLabel as BaseIssueLabel;
use Doctrine\ORM\Mapping as ORM;

/**
 * IssueLabel
 *
 * @ORM\Table(name="issue_label")
 * @ORM\Entity(repositoryClass="VersionContol\GitControlBundle\Repository\IssueLabelRepository")
 */
class IssueLabel extends BaseIssueLabel
{
    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=80, nullable=true)
     */
    private $title;

    /**
     * @var string
     *
     * @ORM\Column(name="hex_color", type="string", length=80, nullable=true)
     */
    private $hexColor;

    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="VersionContol\GitControlBundle\Entity\Issue", mappedBy="issueLabel")
     */
    private $issue;
    
    /**
     * @var \VersionContol\GitControlBundle\Entity\Project
     *
     * @ORM\ManyToOne(targetEntity="VersionContol\GitControlBundle\Entity\Project")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="project_id", referencedColumnName="id")
     * })
     */
    private $project;
    
    /**
     * If set to true its available to all Projects
     * @var boolean
     * @ORM\Column(name="all_projects", type="boolean", nullable=true)
     */
    private $allProjects = false;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->issue = new \Doctrine\Common\Collections\ArrayCollection();
    }


    /**
     * Set title
     *
     * @param string $title
     *
     * @return IssueLabel
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
     * Set hexColor
     *
     * @param string $hexColor
     *
     * @return IssueLabel
     */
    public function setHexColor($hexColor)
    {
        $this->hexColor = $hexColor;

        return $this;
    }

    /**
     * Get hexColor
     *
     * @return string
     */
    public function getHexColor()
    {
        return $this->hexColor;
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
     * Add issue
     *
     * @param \VersionContol\GitControlBundle\Entity\Issue $issue
     *
     * @return IssueLabel
     */
    public function addIssue(\VersionContol\GitControlBundle\Entity\Issues\Issue $issue)
    {
        $this->issue[] = $issue;

        return $this;
    }

    /**
     * Remove issue
     *
     * @param \VersionContol\GitControlBundle\Entity\Issue $issue
     */
    public function removeIssue(\VersionContol\GitControlBundle\Entity\Issues\Issue $issue)
    {
        $this->issue->removeElement($issue);
    }

    /**
     * Get issue
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getIssue()
    {
        return $this->issue;
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
     * 
     * @return boolean
     */
    public function getAllProjects() {
        return $this->allProjects;
    }

    /**
     * 
     * @param boolean $allProjects
     * @return \VersionContol\GitControlBundle\Entity\IssueLabel
     */
    public function setAllProjects($allProjects) {
        $this->allProjects = $allProjects;
        return $this;
    }


}
