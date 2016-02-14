<?php
namespace VersionContol\GitControlBundle\Repository\Issues;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


interface IssueRepositoryInterface{
    
    /**
     * Finds issues for a state
     * @param string $keyword
     * @return array of issues
     */
    public function findIssues($keyword,$state="open");
    
    /**
     * Count number of issues by $state and $keyword
     * @param string $keyword
     * @param string $state
     */
    public function countFindIssues($keyword,$state="open");
    
    /**
     * 
     * @param integer $id
     */
    public function findIssueById($id);
    
    /**
     * Gets a new Issue entity
     * @param type $issue
     * @return VersionContol\GitControlBundle\Entity\Issues\Issue
     */
    public function newIssue();
    
    /**
     * 
     * @param type $issue
     */
    public function createIssue($issue);
    
    /**
     * 
     * @param integer $id
     */
    public function reOpenIssue($id);
    
    /**
     * 
     * @param integer $id
     */
    public function closeIssue($id);
    
    /**
     * 
     * @param integer $issue
     */
    public function updateIssue($issue);
    
      
    
}

