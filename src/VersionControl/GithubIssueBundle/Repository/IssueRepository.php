<?php
namespace VersionControl\GithubIssueBundle\Repository;
use VersionContol\GitControlBundle\Repository\Issues\IssueRepositoryInterface;
use VersionControl\GithubIssueBundle\Entity\Issues\Issue;
use VersionControl\GithubIssueBundle\Entity\Issues\IssueComment;
use VersionControl\GithubIssueBundle\Entity\Issues\IssueLabel;

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
        return $this->mapIssueToEntity($issue);
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
    
    protected function mapIssueToEntity($issue){
        
        $mappedIssue = new Issue();
        $mappedIssue->setId($issue['number']);
        $mappedIssue->setTitle($issue['title']);
        $mappedIssue->setStatus($issue['state']);
        $mappedIssue->setDescription($issue['body']);
        $mappedIssue->setCreatedAt($this->formatDate($issue['created_at']));
        $mappedIssue->setClosedAt($this->formatDate($issue['closed_at']));
        $mappedIssue->setUpdatedAt($this->formatDate($issue['updated_at']));
        
        //Map Issue labels
        if(isset($issue['labels']) && is_array($issue['labels'])){
            foreach($issue['labels'] as $label){
                $issueLabel = $this->mapLabelToEntity($label);
                $mappedIssue->addIssueLabel($issueLabel);
            }
        }
        

        return $mappedIssue;
        
    }
    
    protected function mapLabelToEntity($label){
        
        $mappedIssueLabel = new IssueLabel();
        $mappedIssueLabel->setId($label['name']);
        $mappedIssueLabel->setTitle($label['name']);
        $mappedIssueLabel->setHexColor($label['color']);

        return $mappedIssueLabel;
        
    }
    
    protected function mapEntityToIssue($issueEntity){
        $issue = array(
            'title' =>  $issueEntity->getTitle()
            ,'body' =>  $issueEntity->getDescription()
            ,'state' =>  $issueEntity->getStatus()
            ,'title' =>  $issueEntity->getTitle()
            ,'labels' =>  array()
            //,'milestone' =>  0
        );
        $labels = array();
        foreach($issueEntity->getIssueLabel() as $issueLabel){
            $labels[] = $issueLabel->getId();
        }
        $issue['labels'] = $labels;
        
        return $issue;
        
    }
    
    protected function formatDate($date){
        try {
            $dateTime = new \DateTime($date);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
        return $dateTime;
    }
    
    /**
     * 
     */
    public function newIssueComment(){
        $issueCommentEntity = new IssueComment();
        return $issueCommentEntity;
    }
    

}
