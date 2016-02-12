<?php
namespace VersionContol\GitControlBundle\Repository\Github;
use VersionContol\GitControlBundle\Repository\Issues\IssueRepositoryInterface;
use VersionContol\GitControlBundle\Entity\ProjectIssueIntegrator;
use VersionContol\GitControlBundle\Entity\Issues\Issue;


class IssueRepository implements IssueRepositoryInterface{
    
    /**
     * Github Client
     * @var \Github\Client()
     */
    protected $client;

    /**
     * ProjectIssueIntegrator Entity with data for repo, owner and authentication details
     * @var ProjectIssueIntegrator; 
     */
    protected $issueIntegrator;
    
    public function __construct() {
        $this->client = new \Github\Client();
    }
    
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
        $issueData = $this->client->api('issue')->create($this->issueIntegrator->getOwnerName(), $this->issueIntegrator->getRepoName(), $this->mapEntityToIssue($issueEntity));
    }
    
    /**
     * 
     * @param integer $id
     */
    public function reOpenIssue($id){
        
    }
    
    /**
     * 
     * @param integer $id
     */
    public function closeIssue($id){
        
    }
    
    /**
     * 
     * @param integer $issue
     */
    public function updateIssue($issue){
        
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
        $mappedIssue->setId($issue['id']);
        $mappedIssue->setTitle($issue['title']);
        $mappedIssue->setStatus($issue['state']);
        $mappedIssue->setDescription($issue['body']);
        $mappedIssue->setCreatedAt($this->formatDate($issue['created_at']));
        $mappedIssue->setClosedAt($this->formatDate($issue['closed_at']));
        $mappedIssue->setUpdatedAt($this->formatDate($issue['updated_at']));

        return $mappedIssue;
        
    }
    
    protected function mapEntityToIssue($issueEntity){
        $issue = array(
            'title' =>  $issueEntity->getTitle()
            ,'body' =>  $issueEntity->getDescription()
            ,'state' =>  $issueEntity->getStatus()
            ,'title' =>  $issueEntity->getTitle()
        );

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
    
    public function getIssueIntegrator() {
        return $this->issueIntegrator;
    }

    public function setIssueIntegrator(ProjectIssueIntegrator $issueIntegrator) {
        $this->issueIntegrator = $issueIntegrator;
        return $this;
    }

    protected function authenticate(){
        $this->client->authenticate($this->issueIntegrator->getApiToken(), '', Github\Client::AUTH_URL_TOKEN);
    }

}
