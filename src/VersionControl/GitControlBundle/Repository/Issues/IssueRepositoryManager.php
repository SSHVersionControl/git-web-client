<?php
/*
 * This file is part of the GitControlBundle package.
 *
 * (c) Paul Schweppe <paulschweppe@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace VersionControl\GitControlBundle\Repository\Issues;

use VersionControl\GitControlBundle\Entity\ProjectIssueIntegrator;

use VersionControl\GitControlBundle\Form\IssueType;
use VersionControl\GitControlBundle\Form\IssueEditType;
use VersionControl\GitControlBundle\Form\IssueCommentType;
use VersionControl\GitControlBundle\Form\IssueLabelType;
use VersionControl\GitControlBundle\Form\IssueMilestoneType;

/**
 * Description of IssueRepositoryManager
 *
 * @author paul
 */
class IssueRepositoryManager {
    
    /**
     *
     * @var \VersionControl\GitControlBundle\Entity\ProjectIssueIntegrator 
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
        
    }
            
    public function getIssueRepository(){

        if($this->issueIntegrator){ 
            $repoType = $this->issueIntegrator->getRepoType();
            $issueRepository = $this->serviceContainer->get('version_control.issue_repository.'.strtolower($repoType));
            $issueRepository->setIssueIntegrator($this->issueIntegrator);
        }else{
            //Default ORM repository
            $issueRepository = $this->em->getRepository('VersionControlGitControlBundle:Issue');
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
            $issueLabelRepository = $this->em->getRepository('VersionControlGitControlBundle:IssueLabel');
            $issueLabelRepository->setProject($this->project);
            //Set User
            $user = $this->securityToken->getToken()->getUser();
            $issueLabelRepository->setCurrentUser($user);

        }
        
        return $issueLabelRepository;
    }
    
    public function getIssueMilestoneRepository(){
        if($this->issueIntegrator){ 
            $repoType = $this->issueIntegrator->getRepoType();
            $issueMilestoneRepository = $this->serviceContainer->get('version_control.issue_milestone_repository.'.strtolower($repoType));
            $issueMilestoneRepository->setIssueIntegrator($this->issueIntegrator);
        }else{
            //Default ORM repository
            $issueMilestoneRepository = $this->em->getRepository('VersionControlGitControlBundle:IssueMilestone');
            $issueMilestoneRepository->setProject($this->project);
            //Set User
            $user = $this->securityToken->getToken()->getUser();
            $issueMilestoneRepository->setCurrentUser($user);
        }
        
        return $issueMilestoneRepository;
    }
    
    public function getIssueIntegrator() {
        return $this->issueIntegrator;
    }

    public function setIssueIntegrator(\VersionControl\GitControlBundle\Entity\ProjectIssueIntegrator $issueIntegrator) {
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
    
    public function getIssueCommentFormType(){
        if($this->issueIntegrator){ 
            $repoType = $this->issueIntegrator->getRepoType();
            $issueFormType = $this->serviceContainer->get('version_control.issue_comment_form_type.'.strtolower($repoType));
        }else{
            $issueFormType = new IssueCommentType($this);
        }
        return $issueFormType;
    }
    
    public function getIssueLabelFormType(){
        if($this->issueIntegrator){ 
            $repoType = $this->issueIntegrator->getRepoType();
            $issueFormType = $this->serviceContainer->get('version_control.issue_label_form_type.'.strtolower($repoType));
        }else{
            $issueFormType = new IssueLabelType($this);
        }
        return $issueFormType;
    }
    
    public function getIssueMilestoneFormType(){
        if($this->issueIntegrator){ 
            $repoType = $this->issueIntegrator->getRepoType();
            $issueFormType = $this->serviceContainer->get('version_control.issue_milestone_form_type.'.strtolower($repoType));
        }else{
            $issueFormType = new IssueMilestoneType($this);
        }
        return $issueFormType;
    }


}
