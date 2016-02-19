<?php
namespace VersionControl\GitlabIssueBundle\Repository;
use VersionContol\GitControlBundle\Repository\Issues\IssueRepositoryInterface;
use VersionControl\GitlabIssueBundle\Entity\Issues\Issue;
use VersionControl\GitlabIssueBundle\Entity\Issues\IssueComment;
use VersionControl\GitlabIssueBundle\Entity\Issues\IssueLabel;
use VersionControl\GitlabIssueBundle\DataTransformer\IssueToEntityTransformer;
use VersionControl\GitlabIssueBundle\DataTransformer\IssueCommentToEntityTransformer;

class IssueRepository extends GitlabBase implements IssueRepositoryInterface{
    
    /**
     * Finds issues for a state
     * @param string $keyword
     * @return array of issues
     */
    public function findIssues($keyword = "",$state="open"){
        //$project = new \Gitlab\Model\Project($this->issueIntegrator->getRepoName(), $this->client);
        $this->authenticate();
        $issues = $this->client->api('issues')->all($this->issueIntegrator->getRepoName(), 1, 20,array('state' => $state));
        
        return $this->mapIssues($issues);
    }
    
    public function countFindIssues($keyword,$state="open"){
        $this->authenticate();
        $issues = $this->client->api('issues')->all($this->issueIntegrator->getRepoName(), 1, 20, array('state' => $state));

        return count($issues);
    }
    
    /**
     * 
     * @param integer $id
     */
    public function findIssueById($id){
        $this->authenticate();
        $issue = $this->client->api('issues')->show($this->issueIntegrator->getRepoName(),$id);
        $issueComments = $this->client->api('issues')->showComments( $this->issueIntegrator->getRepoName(), $id);
        return $this->mapIssueToEntity($issue,$issueComments);
    }
    
    /**
     * Gets a new Issue entity
     * @param type $issue
     * @return VersionContol\GitControlBundle\Entity\Issues\Issue
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

        $issue = $this->client->api('issues')->create($this->issueIntegrator->getRepoName(), $this->mapEntityToIssue($issueEntity));
        return $this->mapIssueToEntity($issue);
    }
    
    /**
     * 
     * @param integer $id
     */
    public function reOpenIssue($id){
        $this->authenticate();
        $this->client->api('issues')->update($this->issueIntegrator->getRepoName(),$id,array('state' => 'open'));
    }
    
    /**
     * 
     * @param integer $id
     */
    public function closeIssue($id){
        $this->authenticate();
        $this->client->api('issues')->update( $this->issueIntegrator->getRepoName(),$id,array('state' => 'closed'));

    }
    
    /**
     * 
     * @param integer $issueEntity
     */
    public function updateIssue($issueEntity){
        $this->authenticate();
        $this->client->api('issues')->update( $this->issueIntegrator->getRepoName(), $issueEntity->getId(), $this->mapEntityToIssue($issueEntity));
    }
    
    public function addlabel($issueEntity,$labelEntity){
        $this->authenticate();
        $labels = $this->client->api('issues')->labels()->add( $this->issueIntegrator->getRepoName(), $issueEntity->getId(), $labelEntity->getTitle());
    }
    
    
    /**
     * Gets the number of Issues for a milestone by state
     * @param integer $issueMilestoneId
     * @param string $state open|closed|blank
     */
    public function countIssuesInMilestones($issueMilestoneId,$state){
        $openCount = 0;
        $closedCount = 0;
        $this->authenticate();
        $issues = $this->client->api('milestones')->issues( $this->issueIntegrator->getRepoName(),$issueMilestoneId);
        foreach($issues as $issue){
            if($issue['state'] === 'opened'){
                $openCount++;
            }else if($issue['state'] === 'closed'){
                $closedCount++;
            }
        }
        if($state === 'open'){
            $count = $openCount;
        }else if($state === 'closed'){
            $count = $closedCount;
        }else{
           $count = count($issues); 
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
        $this->authenticate();
        $issues = $this->filterIssuesByState($this->client->api('milestones')->issues( $this->issueIntegrator->getRepoName(),$issueMilestoneId),$state);
         
        return $this->mapIssues($issues);
    }
    
    /**
     * 
     * @param array $issues
     * @param string $state open|closed
     * @return array
     */
    protected function filterIssuesByState($issues,$state){
        $filteredIssues = array();
        foreach($issues as $issue){
            if($issue['state'] === 'opened' && $state === 'open'){
                $filteredIssues[] = $issue;
            }else if($issue['state'] === 'closed' && $state === 'closed'){
                $filteredIssues[] = $issue;
            }
        }
        
        return $filteredIssues;
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
     * @param \VersionControl\GitlabIssueBundle\Entity\Issues\IssueComment $issueCommentEntity
     */
    public function createIssueComment(\VersionControl\GitlabIssueBundle\Entity\Issues\IssueComment $issueCommentEntity){
        $this->authenticate();
        $issueId = $issueCommentEntity->getIssue()->getId();
        $comment = $this->client->api('issues')->addComment( $this->issueIntegrator->getRepoName(), $issueId, $issueCommentEntity->getComment());
        $issueCommentTransfomer = new IssueCommentToEntityTransformer();
        return $issueCommentTransfomer->transform($comment);
    }
    

}
