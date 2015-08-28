<?php

namespace VersionContol\GitControlBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * IssueLabel
 *
 * @ORM\Table(name="issue_label")
 * @ORM\Entity
 */
class IssueLabel
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
    public function addIssue(\VersionContol\GitControlBundle\Entity\Issue $issue)
    {
        $this->issue[] = $issue;

        return $this;
    }

    /**
     * Remove issue
     *
     * @param \VersionContol\GitControlBundle\Entity\Issue $issue
     */
    public function removeIssue(\VersionContol\GitControlBundle\Entity\Issue $issue)
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
}
