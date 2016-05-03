<?php
/*
 * This file is part of the GithubIssueBundle package.
 *
 * (c) Paul Schweppe <paulschweppe@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace VersionControl\GithubIssueBundle\Repository;

use VersionControl\GitControlBundle\Repository\Issues\IssueLabelRepositoryInterface;
use VersionControl\GithubIssueBundle\Entity\Issues\IssueLabel;
use VersionControl\GithubIssueBundle\DataTransformer\IssueLabelToEntityTransformer;

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
        $issueLabelEntity = $this->newLabel();
        $issueLabelEntity->setId($id);
        $issueLabelEntity->setTitle($id);
        
        return $issueLabelEntity;
    }
    
    /**
     * Gets a new Label entity
     * @param type $issue
     * @return VersionControl\GitControlBundle\Entity\Labels\Label
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
        $label = $this->client->api('issue')->labels()->create($this->issueIntegrator->getOwnerName(), $this->issueIntegrator->getRepoName(), array(
            'name' => $issueLabel->getTitle(),
            'color' => $issueLabel->getHexColor(),
        ));
        return $this->mapToEntity($label);
    }

    
    /**
     * 
     * @param integer $issueLabel
     */
    public function updateLabel($issueLabel){
        $this->authenticate();
        $label = $this->client->api('issue')->labels()->update($this->issueIntegrator->getOwnerName(), $this->issueIntegrator->getRepoName(), $issueLabel->getId(), $issueLabel->getTitle(), $issueLabel->getHexColor());
        return $this->mapToEntity($label);
    }
    
    /**
     * 
     * @param integer $issueLabelId
     */
    public function deleteLabel($issueLabelId){
        $this->authenticate();
        $labels = $this->client->api('issue')->labels()->deleteLabel($this->issueIntegrator->getOwnerName(), $this->issueIntegrator->getRepoName(), $issueLabelId);
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

