<?php
/*
 * This file is part of the GitControlBundle package.
 *
 * (c) Paul Schweppe <paulschweppe@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace VersionControl\GitControlBundle\Repository;

use Doctrine\ORM\EntityRepository;
use VersionControl\GitControlBundle\Repository\Issues\IssueLabelRepositoryInterface;
use VersionControl\GitControlBundle\Entity\IssueLabel;

class IssueLabelRepository extends EntityRepository implements IssueLabelRepositoryInterface
{
    
    /**
     *
     * @var VersionControl\GitControlBundle\Entity\Project
     */
    protected $project;
    
    /**
     * @var VersionControl\GitControlBundle\Entity\User\User
     */
    protected $currentUser;
     
    /**
     * Get Project
     */
    public function getProject() {
        return $this->project;
    }
    
    /**
     * Set Project 
     */
    public function setProject($project) {
        $this->project = $project;
        return $this;
    }
    
    /**
     * Get Current User
     */
    public function getCurrentUser() {
        return $this->currentUser;
    }

    /**
     * Set Current User
     */
    public function setCurrentUser($currentUser) {
        $this->currentUser = $currentUser;
        return $this;
    }
    
    /**
     * Get all labels for project
     * 
     * @return array of issueLabels
     */
    public function listLabels(){
        return $this->findByProject($this->project);
    }
     
    /**
     * Get label by id
     * @param integer $id
     */
    public function findLabelById($id){
        return $this->find($id);
    }
    
    /**
     * Gets a new Label entity
     * @param type $issue
     * @return VersionControl\GitControlBundle\Entity\Labels\Label
     */
    public function newLabel(){
        $issueEntity = new IssueLabel();
        $issueEntity->setProject($this->project);
        return $issueEntity;
    }
    
    /**
     * Create a new label
     * @param type $issueLabel
     */
    public function createLabel($issueLabel){
        $em=$this->getEntityManager();
        
        $em->persist($issueLabel);
        $em->flush();
         
        return $issueLabel;
    }

    
    /**
     * Update label
     * @param integer $issue
     */
    public function updateLabel($issueLabel){
        $em=$this->getEntityManager();

        $em->flush();
         
        return $issueLabel;
    }
    
    /**
     * Delete Label
     * @param integer $issueLabelId
     */
    public function deleteLabel($issueLabelId){
        $em=$this->getEntityManager();
        $issueLabel = $this->find($issueLabelId);
        if($issueLabel){
            $em->remove($issueLabel);
            $em->flush();
        }
    }
    
    

  
}
?>