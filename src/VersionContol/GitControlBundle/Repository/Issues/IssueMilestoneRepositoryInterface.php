<?php
namespace VersionContol\GitControlBundle\Repository\Issues;
/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


interface IssueMilestoneRepositoryInterface{
    
    /**
     * Finds issues for a state
     * @param string $keyword
     * @return array of issues
     */
    public function findLabel($keyword,$state="open");
    
    
    
    /**
     * 
     * @param integer $id
     */
    public function findLabelById($id);
    
    /**
     * Gets a new Label entity
     * @param type $issue
     * @return VersionContol\GitControlBundle\Entity\Labels\Label
     */
    public function newLabel();
    
    /**
     * 
     * @param type $issue
     */
    public function createLabel($issue);

    
    /**
     * 
     * @param integer $issue
     */
    public function updateLabel($issue);
    
      
    
}

