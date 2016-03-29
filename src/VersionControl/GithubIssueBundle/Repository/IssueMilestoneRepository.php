<?php
namespace VersionControl\GithubIssueBundle\Repository;

use VersionControl\GitControlBundle\Repository\Issues\IssueMilestoneRepositoryInterface;
use VersionControl\GithubIssueBundle\Entity\Issues\IssueMilestone;
use VersionControl\GithubIssueBundle\DataTransformer\IssueMilestoneToEntityTransformer;


class IssueMilestoneRepository extends GithubBase implements IssueMilestoneRepositoryInterface{
    
    
    
     /**
     * Finds all milestones
     * @param string $state open|closed
     * @return array of issues
     */
    public function listMilestones($state = 'all'){
        
        $milestones = $this->client->api('issue')->milestones()->all($this->issueIntegrator->getOwnerName(), $this->issueIntegrator->getRepoName(), array('state' => $state));

        return $this->mapMilestones($milestones);
    }
    
    /**
     * Get number of milestones for a state
     * @param string $state open|closed
     */
    public function countMilestones($state  = 'all'){
        $milestones = $this->client->api('issue')->milestones()->all($this->issueIntegrator->getOwnerName(), $this->issueIntegrator->getRepoName(), array('state' => $state));

        return count($milestones);
    }
    
    
     
    /**
     * Find milestone for Id
     * @param integer $id
     */
    public function findMilestoneById($id){
        $milestone = $this->client->api('issue')->milestones()->show($this->issueIntegrator->getOwnerName(), $this->issueIntegrator->getRepoName(),$id);
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
        $milestoneData = array();
        $this->authenticate();
        $milestone = $this->client->api('issue')->milestones()->create($this->issueIntegrator->getOwnerName(), $this->issueIntegrator->getRepoName(),$milestoneData);
        return $this->mapToEntity($milestone);
    }

    
    /**
     * 
     * @param integer $issueMilestone
     */
    public function updateMilestone($issueMilestone){
        $milestoneData = array(
                'title'=>$issueMilestone->getTitle()
                ,'description'=>$issueMilestone->getDescription()
                //Date Format YYYY-MM-DDTHH:MM:SSZ
                ,'due_on' => $issueMilestone->getDueOn()->format('c')
                );
        $this->authenticate();
        $milestone = $this->client->api('issue')->milestones()->update($this->issueIntegrator->getOwnerName(), $this->issueIntegrator->getRepoName(),$issueMilestone->getId(),$milestoneData);
        return $this->mapToEntity($milestone);
    }
    
    /**
     * 
     * @param integer $id
     */
    public function deleteMilestone($id){
        $this->authenticate();
        $this->client->api('issue')->milestones()->remove($this->issueIntegrator->getOwnerName(), $this->issueIntegrator->getRepoName(),$id);
        
    }
    
    /**
     * 
     * @param integer $id
     */
    public function reOpenMilestone($id){
        $this->authenticate();
        $this->client->api('issue')->milestones()->update($this->issueIntegrator->getOwnerName(), $this->issueIntegrator->getRepoName(),$id,array('state' => 'open'));
    }
    
    /**
     * 
     * @param integer $id
     */
    public function closeMilestone($id){
        $this->authenticate();
        $this->client->api('issue')->milestones()->update($this->issueIntegrator->getOwnerName(), $this->issueIntegrator->getRepoName(),$id,array('state' => 'closed'));
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
