<?php

namespace VersionControl\GitControlBundle\Entity\Issues;

use Doctrine\ORM\Mapping as ORM;

/**
 * IssueLabel
 *
 */
class IssueLabel
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
    private $hexColor;

    /**
     * @var integer
     *
     */
    private $id;

    /**
     * @var array
     *
     */
    private $issue;
    

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->issue = [];
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
     * Remove issue
     *
     * @param \VersionControl\GitControlBundle\Entity\Issue $issue
     */
    public function removeIssue(\VersionControl\GitControlBundle\Entity\Issues\Issue $issue)
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
