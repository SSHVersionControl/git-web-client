<?php

namespace VersionContol\GitControlBundle\Repository\Issues;

use VersionContol\GitControlBundle\Entity\ProjectIssueIntegrator;

use VersionContol\GitControlBundle\Form\IssueType;
use VersionContol\GitControlBundle\Form\IssueEditType;

/**
 * Description of IssueRepositoryManager
 *
 * @author paul
 */
class IssueRepositoryManager {
    
    /**
     *
     * @var \VersionContol\GitControlBundle\Entity\ProjectIssueIntegrator 
     */
    protected $issueIntegrator;
    
    protected $em;
    
    protected $project;
    
    protected $securityToken;
    
    protected $serviceContainer;


    public function __construct($securityToken, $em, $serviceContainer) {

        $this->securityToken= $securityToken;
        $this->em = $em;
        $this->serviceContainer= $serviceContainer;
        //print_r($this->project->getId());
        //$this->issueIntegrator= $em->getRepository('VersionContolGitControlBundle:ProjectIssueIntegrator')->findOneByProject($project);
        
    }
            
    public function getIssueRepository(){

        if($this->issueIntegrator){ 
            $repoType = $this->issueIntegrator->getRepoType();
            $issueRepository = $this->serviceContainer->get('version_control.issue_repository.'.strtolower($repoType));
            $issueRepository->setIssueIntegrator($this->issueIntegrator);
        }else{
            //Default ORM repository
            $issueRepository = $this->em->getRepository('VersionContolGitControlBundle:Issue');
            $issueRepository->setProject($this->project);
            //Set User
            $user = $this->securityToken->getToken()->getUser();
            $issueRepository->setCurrentUser($user);
        }
        
        return $issueRepository;
    }
    
     public function getIssueLabelRepository(){
        if($this->issueIntegrator){ 
            $repoType = $this->issueIntegrator->getRepoType();
            $issueLabelRepository = $this->serviceContainer->get('version_control.issue_label_repository.'.strtolower($repoType));
            $issueLabelRepository->setIssueIntegrator($this->issueIntegrator);
        }else{
            //Default ORM repository
            $issueLabelRepository = $this->em->getRepository('VersionContolGitControlBundle:Issue');
            $issueLabelRepository->setProject($this->project);

        }
        
        return $issueLabelRepository;
    }
    
    public function getIssueIntegrator() {
        return $this->issueIntegrator;
    }

    public function setIssueIntegrator(\VersionContol\GitControlBundle\Entity\ProjectIssueIntegrator $issueIntegrator) {
        $this->issueIntegrator = $issueIntegrator;
        $this->project = $issueIntegrator->getProject();
        return $this;
    }
    
    public function getProject() {
        return $this->project;
    }

    public function setProject($project) {
        $this->project = $project;
        return $this;
    }

        
    public function getIssueFormType(){
        if($this->issueIntegrator){ 
            $repoType = $this->issueIntegrator->getRepoType();
            $issueFormType = $this->serviceContainer->get('version_control.issue_form_type.'.strtolower($repoType));
        }else{
            $issueFormType = new IssueType($this);
        }
        return $issueFormType;
    }
    
    public function getIssueEditFormType(){

         if($this->issueIntegrator){ 
            $repoType = $this->issueIntegrator->getRepoType();
            $issueEditFormType = $this->serviceContainer->get('version_control.issue_form_edit_type.'.strtolower($repoType));
        }else{
            $issueEditFormType = new IssueEditType($this);
        }
        
        return $issueEditFormType;
    }


}
