<?php
namespace VersionControl\GitControlBundle\Repository\Issues;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


interface IssueMilestoneRepositoryInterface{
    
    /**
     * Finds all milestones
     * @param string $state open|closed
     * @return array of issues
     */
    public function listMilestones($state);
    
    /**
     * Get number of milestones for a state
     * @param string $state open|closed
     */
    public function countMilestones($state);
    
    
     
    /**
     * Find milestone for Id
     * @param integer $id
     */
    public function findMilestoneById($id);
    
    /**
     * Gets a new Milestone entity
     * 
     * @return VersionControl\GitControlBundle\Entity\Milestones\MilestoneInterface
     */
    public function newMilestone();
    
    /**
     * 
     * @param type $issueMilestone
     */
    public function createMilestone($issueMilestone);

    
    /**
     * 
     * @param integer $issueMilestone
     */
    public function updateMilestone($issueMilestone);
    
    /**
     * 
     * @param integer $id
     */
    public function deleteMilestone($id);
    
    /**
     * 
     * @param integer $id
     */
    public function reOpenMilestone($id);
    
    /**
     * 
     * @param integer $id
     */
    public function closeMilestone($id);
    
}

