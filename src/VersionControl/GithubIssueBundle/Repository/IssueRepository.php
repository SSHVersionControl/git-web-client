<?php
namespace VersionControl\GithubIssueBundle\Repository;
use VersionControl\GitControlBundle\Repository\Issues\IssueRepositoryInterface;
use VersionControl\GithubIssueBundle\Entity\Issues\Issue;
use VersionControl\GithubIssueBundle\Entity\Issues\IssueComment;
use VersionControl\GithubIssueBundle\Entity\Issues\IssueLabel;
use VersionControl\GithubIssueBundle\DataTransformer\IssueToEntityTransformer;
use VersionControl\GithubIssueBundle\DataTransformer\IssueCommentToEntityTransformer;

class IssueRepository extends GithubBase implements IssueRepositoryInterface{
    
    /**
     * Finds issues for a state
     * @param string $keyword
     * @return array of issues
     */
    public function findIssues($keyword = "",$state="open"){
        if($keyword){
            $issues = $this->client->api('issue')->find($this->issueIntegrator->getOwnerName(), $this->issueIntegrator->getRepoName(), $state, $keyword);
        }else{
  
            $issues = $this->client->api('issue')->all($this->issueIntegrator->getOwnerName(), $this->issueIntegrator->getRepoName(), array('state' => $state));
        }
        
        return $this->mapIssues($issues);
    }
    
    public function countFindIssues($keyword,$state="open"){
         if($keyword){
            $issues = $this->client->api('issue')->find($this->issueIntegrator->getOwnerName(), $this->issueIntegrator->getRepoName(), $state, $keyword);
        }else{
            $issues = $this->client->api('issue')->all($this->issueIntegrator->getOwnerName(), $this->issueIntegrator->getRepoName(), array('state' => $state));
        }
        
        return count($issues);
    }
    
    /**
     * 
     * @param integer $id
     */
    public function findIssueById($id){
        $issue = $this->client->api('issue')->show($this->issueIntegrator->getOwnerName(), $this->issueIntegrator->getRepoName(), $id);
        $issueComments = $this->client->api('issue')->comments()->all($this->issueIntegrator->getOwnerName(), $this->issueIntegrator->getRepoName(), $id);
        return $this->mapIssueToEntity($issue,$issueComments);
    }
    
    /**
     * Gets a new Issue entity
     * @param type $issue
     * @return VersionControl\GitControlBundle\Entity\Issues\Issue
     */
    public function newIssue(){
        $issueEntity = new Issue();
        return $issueEntity;
    }
    
    /**
     * 
     * @param type $issueEntity
     */
    public function createIssue($issueEntity){
        $this->authenticate();
        $issue = $this->client->api('issue')->create($this->issueIntegrator->getOwnerName(), $this->issueIntegrator->getRepoName(), $this->mapEntityToIssue($issueEntity));
        return $this->mapIssueToEntity($issue);
    }
    
    /**
     * 
     * @param integer $id
     */
    public function reOpenIssue($id){
        $this->authenticate();
        $this->client->api('issue')->update($this->issueIntegrator->getOwnerName(), $this->issueIntegrator->getRepoName(),$id,array('state' => 'open'));
    }
    
    /**
     * 
     * @param integer $id
     */
    public function closeIssue($id){
        $this->authenticate();
        $this->client->api('issue')->update($this->issueIntegrator->getOwnerName(), $this->issueIntegrator->getRepoName(),$id,array('state' => 'closed'));

    }
    
    /**
     * 
     * @param integer $issueEntity
     */
    public function updateIssue($issueEntity){
        $this->authenticate();
        $this->client->api('issue')->update($this->issueIntegrator->getOwnerName(), $this->issueIntegrator->getRepoName(), $issueEntity->getId(), $this->mapEntityToIssue($issueEntity));
    }
    
    public function addlabel($issueEntity,$labelEntity){
        $this->authenticate();
        $labels = $this->client->api('issue')->labels()->add($this->issueIntegrator->getOwnerName(), $this->issueIntegrator->getRepoName(), $issueEntity->getId(), $labelEntity->getTitle());
    }
    
    
    /**
     * Gets the number of Issues for a milestone by state
     * @param integer $issueMilestoneId
     * @param string $state open|closed|blank
     */
    public function countIssuesInMilestones($issueMilestoneId,$state){
        $milestone = $this->client->api('issue')->milestones()->show($this->issueIntegrator->getOwnerName(), $this->issueIntegrator->getRepoName(),$issueMilestoneId);
        if($state === 'open'){
            $count = $milestone['open_issues'];
        }else if($state === 'closed'){
            $count = $milestone['closed_issues'];
        }else{
           $count = $milestone['open_issues']+$milestone['closed_issues']; 
        }
        return $count;
    }
    
    /**
     * Find issues in milestone
     * 
     * @param integer $issueMilestoneId
     * @param string $state open|closed|blank
     * @param string $keyword Search string
     */
    public function findIssuesInMilestones($issueMilestoneId,$state,$keyword = false){
        $issues = $this->client->api('issue')->all($this->issueIntegrator->getOwnerName(), $this->issueIntegrator->getRepoName(), array('state' => $state, 'milestone' => $issueMilestoneId));

        return $this->mapIssues($issues);
    }
    
    /**
     * 
     * @param array $issues
     * @return array of 
     */
    protected function mapIssues($issues){
        $issueEntities = array();
        if(is_array($issues)){
            foreach($issues as $issue){
                $issueEntities[] =  $this->mapIssueToEntity($issue);
            }
        }
        
        return $issueEntities;
    }
    
    protected function mapIssueToEntity($issue,$issueComments = array()){
        $issueTransfomer = new IssueToEntityTransformer();
        $issueCommentTransfomer = new IssueCommentToEntityTransformer();
        $issueEntity = $issueTransfomer->transform($issue);
        
        foreach($issueComments as $issueComment){
            $issueCommentEntity = $issueCommentTransfomer->transform($issueComment);
            $issueEntity->addIssueComment($issueCommentEntity);
        }
        
        return $issueEntity;
    }
    
    
    protected function mapEntityToIssue($issueEntity){
        $issueTransfomer = new IssueToEntityTransformer();
        return $issueTransfomer->reverseTransform($issueEntity);
    }

    /**
     * 
     */
    public function newIssueComment(){
        $issueCommentEntity = new IssueComment();
        return $issueCommentEntity;
    }
    
    /**
     * Creates a New issue Comment on github
     * @param \VersionControl\GithubIssueBundle\Entity\Issues\IssueComment $issueCommentEntity
     */
    public function createIssueComment(\VersionControl\GithubIssueBundle\Entity\Issues\IssueComment $issueCommentEntity){
        $this->authenticate();
        $issueId = $issueCommentEntity->getIssue()->getId();
        $comment = $this->client->api('issue')->comments()->create($this->issueIntegrator->getOwnerName(), $this->issueIntegrator->getRepoName(), $issueId, array('body' => $issueCommentEntity->getComment()));
        $issueCommentTransfomer = new IssueCommentToEntityTransformer();
        return $issueCommentTransfomer->transform($comment);
    }
    

}
