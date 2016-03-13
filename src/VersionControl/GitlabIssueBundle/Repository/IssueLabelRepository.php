<?php
namespace VersionControl\GitlabIssueBundle\Repository;

use VersionContol\GitControlBundle\Repository\Issues\IssueLabelRepositoryInterface;
use VersionControl\GitlabIssueBundle\Entity\Issues\IssueLabel;
use VersionControl\GitlabIssueBundle\DataTransformer\IssueLabelToEntityTransformer;

class  IssueLabelRepository extends GitlabBase implements IssueLabelRepositoryInterface{
    

     /**
     * Finds issues for a state
     * @param string $keyword
     * @return array of issues
     */
    public function listLabels(){
        $this->authenticate();
        $labels = $this->client->api('projects')->labels($this->issueIntegrator->getProjectName());

        return $this->mapLabels($labels);
    }
    
    /**
     * 
     * @param integer $id
     */
    public function findLabelById($id){
        $issueLabelEntity = $this->newLabel();
        $issueLabelEntity->setId($id);
        $issueLabelEntity->setTitle($id);
        
        return $issueLabelEntity;
    }
    
    /**
     * Gets a new Label entity
     * @param type $issue
     * @return VersionContol\GitControlBundle\Entity\Labels\Label
     */
    public function newLabel(){
        $issueLabelEntity = new IssueLabel();
        return $issueLabelEntity;
    }

    /**
     * 
     * @param type $issueLabel
     */
    public function createLabel($issueLabel){
        $this->authenticate();
        $label = $this->client->api('projects')->addLabel($this->issueIntegrator->getProjectName(), array(
            'name' => $issueLabel->getTitle(),
            'color' => '#'.$issueLabel->getHexColor(),
        ));
        return $this->mapToEntity($label);
    }

    
    /**
     * 
     * @param integer $issueLabel
     */
    public function updateLabel($issueLabel){
        $this->authenticate();
        
        $label = $this->client->api('projects')->updateLabel($this->issueIntegrator->getProjectName(), array(
            'name' => $issueLabel->getId(),
            'new_name' => $issueLabel->getTitle(),
            'color' => '#'.$issueLabel->getHexColor(),
        ));
        return $this->mapToEntity($label);
    }
    
    /**
     * 
     * @param integer $issueLabelId
     */
    public function deleteLabel($issueLabelId){
        $this->authenticate();
        $this->client->api('projects')->removeLabel($this->issueIntegrator->getProjectName(), $issueLabelId);
        return true;
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
        
        $issueLabelTransfomer = new IssueLabelToEntityTransformer();
        $issueLabelEntity = $issueLabelTransfomer->transform($label);

        return $issueLabelEntity;
        
    }
    
      
    
}

