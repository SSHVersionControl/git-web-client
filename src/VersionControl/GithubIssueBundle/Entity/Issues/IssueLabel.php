<?php
/*
 * This file is part of the GithubIssueBundle package.
 *
 * (c) Paul Schweppe <paulschweppe@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace VersionControl\GithubIssueBundle\Entity\Issues;

use VersionControl\GitControlBundle\Entity\Issues\IssueLabelInterface;
/**
 * IssueLabel
 *
 */
class IssueLabel implements IssueLabelInterface
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
    private $issues;
    

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->issues = [];
    }
    
     public function setId($id) {
        $this->id = $id;
        return $this;
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
     * Get issue
     *
     * @return array
     */
    public function getIssues()
    {
        return $this->issues;
    }

    


}
