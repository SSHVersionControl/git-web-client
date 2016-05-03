<?php
/*
 * This file is part of the GitlabIssueBundle package.
 *
 * (c) Paul Schweppe <paulschweppe@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace VersionControl\GitlabIssueBundle\Repository;

use VersionControl\GitControlBundle\Repository\Issues\IssueMilestoneRepositoryInterface;
use VersionControl\GitlabIssueBundle\Entity\Issues\IssueMilestone;
use VersionControl\GitlabIssueBundle\DataTransformer\IssueMilestoneToEntityTransformer;


class IssueMilestoneRepository extends GitlabBase implements IssueMilestoneRepositoryInterface{
    
    
    
     /**
     * Finds all milestones
     * @param string $state open|closed
     * @return array of issues
     */
    public function listMilestones($state = 'all'){
        $this->authenticate();
        $milestones = $this->client->api('milestones')->all($this->issueIntegrator->getProjectName(),1,2000, array('state' => $state));

        return $this->mapMilestones($milestones);
    }
    
    /**
     * Get number of milestones for a state
     * @param string $state open|closed
     */
    public function countMilestones($state  = 'all'){
        $this->authenticate();
        $milestones = $this->client->api('milestones')->all($this->issueIntegrator->getProjectName(),1,2000, array('state' => $state));

        return count($milestones);
    }
    
    
     
    /**
     * Find milestone for Id
     * @param integer $id
     */
    public function findMilestoneById($id){
        $this->authenticate();
        $milestone = $this->client->api('milestones')->show($this->issueIntegrator->getProjectName(),$id);
        return $this->mapToEntity($milestone);
    }
    
    /**
     * Gets a new Milestone entity
     * 
     * @return VersionControl\GitControlBundle\Entity\Milestones\MilestoneInterface
     */
    public function newMilestone(){
        $issueMilestoneEntity = new IssueMilestone();
        return $issueMilestoneEntity;
    }
    
    /**
     * 
     * @param type $issueMilestone
     */
    public function createMilestone($issueMilestone){
        $milestoneData = array(
            'title'=>$issueMilestone->getTitle()
            ,'description'=>$issueMilestone->getDescription()
            //Date Format YYYY-MM-DDTHH:MM:SSZ
            ,'due_date' => $issueMilestone->getDueOn()->format('c')
        );
        $this->authenticate();
        $milestone = $this->client->api('milestones')->create($this->issueIntegrator->getProjectName(),$milestoneData);
        return $this->mapToEntity($milestone);
    }

    
    /**
     * 
     * @param integer $issueMilestone
     */
    public function updateMilestone($issueMilestone){
        $milestoneData = array(
                'milestone_id'=>$issueMilestone->getId()
                ,'title'=>$issueMilestone->getTitle()
                ,'description'=>$issueMilestone->getDescription()
                //Date Format YYYY-MM-DDTHH:MM:SSZ
                ,'due_date' => $issueMilestone->getDueOn()->format('c')
                );
        $this->authenticate();
        $milestone = $this->client->api('milestones')->update($this->issueIntegrator->getProjectName(),$issueMilestone->getId(),$milestoneData);
        return $this->mapToEntity($milestone);
    }
    
    /**
     * 
     * @param integer $id
     */
    public function deleteMilestone($id){
        $this->authenticate();
        $this->client->api('milestones')->remove($this->issueIntegrator->getProjectName(),$id);
        
    }
    
    /**
     * 
     * @param integer $id
     */
    public function reOpenMilestone($id){
        $this->authenticate();
        $this->client->api('milestones')->update($this->issueIntegrator->getProjectName(),array('milestone_id'=>$id,'state_event' => 'activate'));
    }
    
    /**
     * 
     * @param integer $id
     */
    public function closeMilestone($id){
        $this->authenticate();
        $this->client->api('milestones')->update($this->issueIntegrator->getProjectName(),array('milestone_id'=>$id,'state_event' => 'close'));
    }

    
    /**
     * 
     * @param array $issues
     * @return array of 
     */
    protected function mapMilestones($milestones){
        $issueMilestoneEntities = array();
        if(is_array($milestones)){
            foreach($milestones as $milestone){
                $issueMilestoneEntities[] =  $this->mapToEntity($milestone);
            }
        }
        
        return $issueMilestoneEntities;
    }
    
    protected function mapToEntity($milestone){
        $issueMilestoneTransfomer = new IssueMilestoneToEntityTransformer();
        $issueMilestoneEntity = $issueMilestoneTransfomer->transform($milestone);

        
        return $issueMilestoneEntity;
    }
    
    
    protected function mapEntityToIssue($issueMilestoneEntity){
        $issueMilestoneTransfomer = new IssueMilestoneToEntityTransformer();
        return $issueMilestoneTransfomer->reverseTransform($issueMilestoneEntity);
    }

    
}
