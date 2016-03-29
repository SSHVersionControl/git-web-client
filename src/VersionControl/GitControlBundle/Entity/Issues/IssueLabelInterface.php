<?php

namespace VersionControl\GitControlBundle\Entity\Issues;


/**
 * IssueLabel
 *
 */
interface IssueLabelInterface
{


    /**
     * Get title
     *
     * @return string
     */
    public function getTitle();

    /**
     * Get hexColor
     *
     * @return string
     */
    public function getHexColor();

    /**
     * Get id
     *
     * @return integer
     */
    public function getId();


    /**
     * Get issue
     *
     * @return array
     */
    public function getIssues();

    


}
