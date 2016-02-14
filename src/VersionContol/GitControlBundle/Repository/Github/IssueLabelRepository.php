<?php
namespace VersionContol\GitControlBundle\Repository\Github;

use VersionContol\GitControlBundle\Repository\Issues\IssueLabelRepositoryInterface;
use VersionContol\GitControlBundle\Entity\Issues\IssueLabel;

class  IssueLabelRepository extends GithubBase implements IssueLabelRepositoryInterface{
    

     /**
     * Finds issues for a state
     * @param string $keyword
     * @return array of issues
     */
    public function listLabels(){
        $labels = $this->client->api('issue')->labels()->all($this->issueIntegrator->getOwnerName(), $this->issueIntegrator->getRepoName());

        return $this->mapLabels($labels);
    }
    
    /**
     * 
     * @param integer $id
     */
    public function findLabelById($id){
        
    }
    
    /**
     * Gets a new Label entity
     * @param type $issue
     * @return VersionContol\GitControlBundle\Entity\Labels\Label
     */
    public function newLabel(){
        
    }
    
    /**
     * 
     * @param type $issueLabel
     */
    public function createLabel($issueLabel){
        $label = $this->client->api('issue')->labels()->create($this->issueIntegrator->getOwnerName(), $this->issueIntegrator->getRepoName(), array(
            'name' => $issueLabel->title(),
            'color' => $issueLabel->hexColor(),
        ));
    }

    
    /**
     * 
     * @param integer $issueLabel
     */
    public function updateLabel($issueLabel){
        $labels = $this->client->api('issue')->labels()->update('KnpLabs', 'php-github-api', $issueLabel->title(), $issueLabel->hexColor());

    }
    
    /**
     * 
     * @param array $issues
     * @return array of 
     */
    protected function mapLabels($labels){
        $issueLabelEntities = array();
 
        if(is_array($labels)){
            foreach($labels as $label){
                $issueLabelEntities[] =  $this->mapToEntity($label);
            }
        }
        
        return $issueLabelEntities;
    }
    
    protected function mapToEntity($label){
        
        $mappedIssueLabel = new IssueLabel();
        $mappedIssueLabel->setId($label['name']);
        $mappedIssueLabel->setTitle($label['name']);
        $mappedIssueLabel->setHexColor($label['color']);

        return $mappedIssueLabel;
        
    }
    
      
    
}

