<?php
/*
 * This file is part of the GitControlBundle package.
 *
 * (c) Paul Schweppe <paulschweppe@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace VersionControl\GitControlBundle\Entity;

use VersionControl\GitControlBundle\Entity\Issues\IssueLabelInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * IssueLabel.
 *
 * @ORM\Table(name="issue_label")
 * @ORM\Entity(repositoryClass="VersionControl\GitControlBundle\Repository\IssueLabelRepository")
 */
class IssueLabel implements IssueLabelInterface
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
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \Doctrine\Common\Collections\Collection
     *
     * @ORM\ManyToMany(targetEntity="VersionControl\GitControlBundle\Entity\Issue", mappedBy="issueLabel")
     */
    private $issue;

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
     * If set to true its available to all Projects.
     *
     * @var bool
     * @ORM\Column(name="all_projects", type="boolean", nullable=true)
     */
    private $allProjects = false;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->issue = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Set title.
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
     * Get title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set hexColor.
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
     * Get hexColor.
     *
     * @return string
     */
    public function getHexColor()
    {
        return $this->hexColor;
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Add issue.
     *
     * @param \VersionControl\GitControlBundle\Entity\Issue $issue
     *
     * @return IssueLabel
     */
    public function addIssue(\VersionControl\GitControlBundle\Entity\Issues\Issue $issue)
    {
        $this->issue[] = $issue;

        return $this;
    }

    /**
     * Remove issue.
     *
     * @param \VersionControl\GitControlBundle\Entity\Issue $issue
     */
    public function removeIssue(\VersionControl\GitControlBundle\Entity\Issues\Issue $issue)
    {
        $this->issue->removeElement($issue);
    }

    /**
     * Get issue.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getIssue()
    {
        return $this->issue;
    }

    /**
     * Get issue.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getIssues()
    {
        return $this->issue;
    }

    /**
     * Set project.
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
     * Get project.
     *
     * @return \VersionControl\GitControlBundle\Entity\Project
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * @return bool
     */
    public function getAllProjects()
    {
        return $this->allProjects;
    }

    /**
     * @param bool $allProjects
     *
     * @return \VersionControl\GitControlBundle\Entity\IssueLabel
     */
    public function setAllProjects($allProjects)
    {
        $this->allProjects = $allProjects;

        return $this;
    }
}
