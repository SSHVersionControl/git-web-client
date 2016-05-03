<?php
/*
 * This file is part of the GitControlBundle package.
 *
 * (c) Paul Schweppe <paulschweppe@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace VersionControl\GitControlBundle\Repository\Issues;



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

