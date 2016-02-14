<?php

namespace VersionControl\GithubIssueBundle\Entity\Issues;

use VersionContol\GitControlBundle\Entity\Issues\IssueLabel as BaseIssueLabel;
/**
 * IssueLabel
 *
 */
class IssueLabel extends BaseIssueLabel
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
     * @param \VersionContol\GitControlBundle\Entity\Issue $issue
     *
     * @return IssueLabel
     */
    public function addIssue(\VersionContol\GitControlBundle\Entity\Issues\Issues $issue)
    {
        $this->issue[] = $issue;

        return $this;
    }

    /**
     * Remove issue
     *
     * @param \VersionContol\GitControlBundle\Entity\Issue $issue
     */
    public function removeIssue(\VersionContol\GitControlBundle\Entity\Issues\Issues $issue)
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
