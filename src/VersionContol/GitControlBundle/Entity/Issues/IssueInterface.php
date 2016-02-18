<?php

namespace VersionContol\GitControlBundle\Entity\Issues;


interface IssueInterface
{

    /**
     * Get id
     *
     * @return integer
     */
    public function getId();

    /**
     * Get title
     *
     * @return string
     */
    public function getTitle();



    /**
     * Get description
     *
     * @return string
     */
    public function getDescription();



    /**
     * Get status
     *
     * @return string
     */
    public function getStatus();



    /**
     * Get closedAt
     *
     * @return \DateTime
     */
    public function getClosedAt();


    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt();



    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt();


    /**
     * Get issueMilestone
     *
     * @return \VersionContol\GitControlBundle\Entity\Issues\IssueMilestone
     */
    public function getIssueMilestone();



    /**
     * Get project
     * Not sure if this is need
     * @return \VersionContol\GitControlBundle\Entity\Project
     */
    public function getProject();


    /**
     * Get User
     *
     * @return \VersionContol\GitControlBundle\Entity\Issues\IssueUserInterface
     */
    public function getUser();

    

    /**
     * Get issueLabel
     *
     * @return array of \VersionContol\GitControlBundle\Entity\Issue\IssueLabelInteface
     */
    public function getIssueLabel();
    
    /**
     * Get Issue Comments
     * @return array of \VersionContol\GitControlBundle\Entity\Issue\IssueCommentInteface
     */
    public function getIssueComments();


    /**
     * Set status
     *
     * @param string $status
     *
     * @return Issue
     */
    public function setClosed();
    
    /**
     * Set status
     *
     * @param string $status
     *
     * @return Issue
     */
    public function setOpen();
    
    /**
     * 
     */
    public function isClosed();

}


