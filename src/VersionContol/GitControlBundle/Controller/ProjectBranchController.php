<?php

namespace VersionContol\GitControlBundle\Controller;

use VersionContol\GitControlBundle\Controller\Base\BaseProjectController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use VersionContol\GitControlBundle\Entity\Project;
use VersionContol\GitControlBundle\Form\ProjectType;
use VersionContol\GitControlBundle\Utility\GitCommands;
use Symfony\Component\Validator\Constraints\NotBlank;
use VersionContol\GitControlBundle\Entity\UserProjects;
use VersionContol\GitControlBundle\Utility\GitCommands\GitCommand;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;

use VersionContol\GitControlBundle\Annotation\ProjectAccess;
 /** ///Route("/example", service="example_bundle.controller.example_controller") */

/**
 * Project controller.
 *
 * @Route("/project/{id}/branch")
 */
class ProjectBranchController extends BaseProjectController
{
    
    
    /**
     *
     * @var GitCommand 
     */
    protected $gitBranchCommands;
    

    protected $projectGrantType = 'EDIT';




    /**
     * List Branches. Not sure how to list remote and local branches.
     *
     * @Route("es/{newBranchName}", name="project_branches")
     * @Method("GET")
     * @Template()
     * @ProjectAccess(grantType="VIEW")
     */
    public function branchesAction($id,$newBranchName = false)
    {
        
        $this->initListingView();
        
        $defaultData = array();
        if($newBranchName !== false){
            $defaultData['name'] = $newBranchName; 
        }
        
        $form = $this->createNewBranchForm($this->project,$defaultData);
  
        return array_merge($this->viewVariables, array(
            'form' => $form->createView(),
            'newBranchName' => $newBranchName,
        ));
    }
    
    

    /**
     * Pulls git repository from remote to local.
     *
     * @Route("/create/", name="project_branch")
     * @Method("POST")
     * @Template("VersionContolGitControlBundle:ProjectBranch:branches.html.twig")
     * @ProjectAccess(grantType="EDIT")
     */
    public function createBranchAction(Request $request,$id)
    {

        
        $form = $this->createNewBranchForm($this->project);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $newBranchName = $data['name'];
            $switchToBranch= $data['switch'];
            try{
                
                $response = $this->gitBranchCommands->createLocalBranch($newBranchName,$switchToBranch);
                $this->get('session')->getFlashBag()->add('notice', $response);
                return $this->redirect($this->generateUrl('project_branches', array('id' => $id)));
                
            }catch(\Exception $e){
               $this->get('session')->getFlashBag()->add('error', $e->getMessage()); 
            }
 
        }
        
        $this->initListingView();

        return array_merge($this->viewVariables, array(
            'form' => $form->createView(),

        ));
    }
    
    /**
     * Pulls git repository from remote to local.
     *
     * @Route("/checkoutbranch/{branchName}", name="project_checkoutbranch" , requirements={"branchName"=".+"})
     * @Method("GET")
     * @Template("VersionContolGitControlBundle:Project:branches.html.twig")
     * @ProjectAccess(grantType="EDIT")
     */
    public function checkoutBranchAction($id, $branchName){
        
        
        $response = $this->gitBranchCommands->checkoutBranch($branchName);
        
        $this->get('session')->getFlashBag()->add('notice', $response);
        
        return $this->redirect($this->generateUrl('project_branches', array('id' => $id)));
    }
    
    /**
     * List Branches. Not sure how to list remote and local branches.
     *
     * @Route("/remotes", name="project_branch_remotes")
     * @Method("GET")
     * @Template()
     * @ProjectAccess(grantType="VIEW")
     */
    public function remoteBranchesAction($id)
    {
        
        $branchName = $this->gitBranchCommands->getCurrentBranch();
        
        //Remote Server choice 
        $gitRemoteBranches = $this->gitBranchCommands->getBranchRemoteListing();
        
        $form = $this->createNewBranchForm($this->project,array(),'project_branch_remote_checkout');
        $form->add('remotename', 'hidden', array(
                'label' => 'Remote Branch Name'
                ,'required' => true
                ,'constraints' => array(
                    new NotBlank()
                ))
            );  
        
        return array(
            'project'      => $this->project,
            'branches' => $gitRemoteBranches,
            'branchName' => $branchName,
            'form' => $form->createView(),
        );
    }
    
    /**
     * Pulls git repository from remote to local.
     *
     * @Route("/checkout-remote", name="project_branch_remote_checkout")
     * @Method("POST")
     * @Template("VersionContolGitControlBundle:ProjectBranch:remoteBranches.html.twig")
     * @ProjectAccess(grantType="EDIT")
     */
    public function checkoutRemoteBranchAction(Request $request,$id)
    {
        
        $branchName = $this->gitBranchCommands->getCurrentBranch();
         $gitRemoteBranches = $this->gitBranchCommands->getBranchRemoteListing();

        $form = $this->createNewBranchForm($this->project,array(),'project_branch_remote_checkout');
        $form->add('remotename', 'hidden', array(
                'label' => 'Remote Branch Name'
                ,'required' => true
                ,'constraints' => array(
                    new NotBlank()
                ))
            );  
        
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $newBranchName = $data['name'];
            $remoteBranchName = $data['remotename'];
            $switchToBranch= $data['switch'];
            
            try{
                $response = $this->gitBranchCommands->createBranchFromRemote($newBranchName,$remoteBranchName,$switchToBranch);
                $this->get('session')->getFlashBag()->add('notice', $response);
                return $this->redirect($this->generateUrl('project_branch_remotes', array('id' => $id)));
                
            }catch(\Exception $e){
               $this->get('session')->getFlashBag()->add('error', $e->getMessage()); 
            }
            
            
        }

        return array(
           'project'      => $this->project,
            'branches' => $gitRemoteBranches,
            'branchName' => $branchName,
            'form' => $form->createView(),
        );
    }
    
    /**
     * Pulls git repository from remote to local.
     *
     * @Route("/fetchall/", name="project_branch_fetchall")
     * @Method("GET")
     * @Template("VersionContolGitControlBundle:ProjectBranch:remoteBranches.html.twig")
     * @ProjectAccess(grantType="EDIT")
     */
    public function fetchAllAction($id){
        
        
        $response = $this->gitBranchCommands->fetchAll();
        
        $this->get('session')->getFlashBag()->add('notice', $response);
        
        return $this->redirect($this->generateUrl('project_branch_remotes', array('id' => $id)));
    }
    
    
    /**
     * Pulls git repository from remote to local.
     *
     * @Route("/deletebranch/{branchName}", name="project_deletebranch" , requirements={"branchName"=".+"})
     * @Method("GET")
     * @Template("VersionContolGitControlBundle:Project:branches.html.twig")
     * @ProjectAccess(grantType="MASTER")
     */
    public function deleteBranchAction($id, $branchName){
        
        $response = $this->gitBranchCommands->deleteBranch($branchName);
        
        $this->get('session')->getFlashBag()->add('notice', $response);
        
        return $this->redirect($this->generateUrl('project_branches', array('id' => $id)));
    }
    
    
    
    
    
    /**
    * Creates a form to edit a Project entity.
    *
    * @param Project $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createNewBranchForm($project,$defaultData = array(),$formAction = 'project_branch')
    {

        //$defaultData = array();
        $form = $this->createFormBuilder($defaultData, array(
                'action' => $this->generateUrl($formAction, array('id' => $project->getId())),
                'method' => 'POST',
            ))
            ->add('name', 'text', array(
                'label' => 'Branch Name'
                ,'required' => true
                ,'constraints' => array(
                    new NotBlank()
                ))
            )   
            ->add('switch', 'checkbox', array(
                'label' => 'Switch to branch on creation'
                ,'required' => false
                )
            )   
            ->getForm();

        $form->add('submit', 'submit', array('label' => 'Create'));
        return $form;
    }
    
    /**
     * Pulls git repository from remote to local.
     *
     * @Route("/mergebranch/{branchName}", name="project_mergebranch" , requirements={"branchName"=".+"})
     * @Method("GET")
     * @Template("VersionContolGitControlBundle:Project:branches.html.twig")
     * @ProjectAccess(grantType="MASTER")
     */
    public function mergeBranchAction($id,$branchName){
        
        $response = $this->gitBranchCommands->mergeBranch($branchName);
            
        $this->get('session')->getFlashBag()->add('notice', $response);
            
        return $this->redirect($this->generateUrl('project_branches'));

    }
    
    private function getBranchesToMerge(){
        
        $gitLocalBranches = $this->gitBranchCommands->getBranches(true);
        $currentbranchName = $this->gitBranchCommands->getCurrentBranch();
        $mergeBranches = array();
        foreach($gitLocalBranches as $branchName){
            if($branchName !== $currentbranchName){
                $mergeBranches[$branchName] = $branchName;
            }
        }
        
        return $mergeBranches;
    }
    
    /**
    * Creates a form to edit a Project entity.
    *
    * @param Project $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createMergeBranchForm($project,$branches,$formAction = 'project_mergebranch')
    {

        $defaultData = array();
        $form = $this->createFormBuilder($defaultData, array(
                'action' => $this->generateUrl($formAction, array('id' => $project->getId())),
                'method' => 'POST',
            ))
            ->add('branch', 'choice', array(
                'choices' => $branches
                ,'label' => 'Branch Name'
                ,'required' => true
                ,'choices_as_values' => true
                ,'constraints' => array(
                    new NotBlank()
                ))
            )   
            ->getForm();

        $form->add('submit', 'submit', array('label' => 'Merge'));
        return $form;
    }
    
    /**
     * 
     * @param integer $id
     */
    public function initAction($id, $grantType = 'VIEW'){
 
        parent::initAction($id,$grantType);
        
        $this->gitBranchCommands = $this->get('version_control.git_branch')->setProject($this->project);

    }
    
    protected function initListingView(){
        
        $branchName = $this->gitBranchCommands->getCurrentBranch();
        //Local Server choice 
        $gitLocalBranches = $this->gitBranchCommands->getBranches(true);
        
        $gitLogCommand = $this->get('version_control.git_log')->setProject($this->project);

 
        $gitLogCommand->setBranch($branchName)->setLogCount(1);

        
        $gitLogs = $gitLogCommand->execute()->getFirstResult();
        
        //$gitLogs = $this->gitCommands->getLog(1,$branchName);

        $this->viewVariables = array_merge($this->viewVariables, array(
            'branches' => $gitLocalBranches,
            'gitLogs' => array($gitLogs)
        ));
    }
    
    
}

