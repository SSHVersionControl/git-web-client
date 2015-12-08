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
 /** ///Route("/example", service="example_bundle.controller.example_controller") */

/**
 * Project controller.
 *
 * @Route("/branch")
 */
class ProjectBranchController extends BaseProjectController
{
    
    /**
     *
     * @var GitCommand 
     */
    protected $gitCommands;
    
    /**
     *
     * @var GitCommand 
     */
    protected $gitBranchCommands;
    
    /**
     * The current Project
     * @var Project 
     */
    protected $project;
   
    
    /**
     * List Branches. Not sure how to list remote and local branches.
     *
     * @Route("es/{id}", name="project_branches")
     * @Method("GET")
     * @Template()
     */
    public function branchesAction($id)
    {
        
        $this->initAction($id);
        $this->initListingView();
        
        $form = $this->createNewBranchForm($this->project);
  
        return array_merge($this->viewVariables, array(
            'form' => $form->createView(),
        ));
    }
    
    

    /**
     * Pulls git repository from remote to local.
     *
     * @Route("/create/{id}", name="project_branch")
     * @Method("POST")
     * @Template("VersionContolGitControlBundle:ProjectBranch:branches.html.twig")
     */
    public function createBranchAction(Request $request,$id)
    {
        $this->initAction($id);
        
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
     * @Route("/checkoutbranch/{id}/{branchName}", name="project_checkoutbranch" , requirements={"branchName"=".+"})
     * @Method("GET")
     * @Template("VersionContolGitControlBundle:Project:branches.html.twig")
     */
    public function checkoutBranchAction($id, $branchName){
        
        $this->initAction($id);
        
        $response = $this->gitBranchCommands->checkoutBranch($branchName);
        
        $this->get('session')->getFlashBag()->add('notice', $response);
        
        return $this->redirect($this->generateUrl('project_branches', array('id' => $id)));
    }
    
    /**
     * List Branches. Not sure how to list remote and local branches.
     *
     * @Route("/remotes/{id}", name="project_branch_remotes")
     * @Method("GET")
     * @Template()
     */
    public function remoteBranchesAction($id)
    {
        
        $this->initAction($id);
        
        $branchName = $this->gitBranchCommands->getCurrentBranch();
        
        //Remote Server choice 
        $gitRemoteBranches = $this->gitBranchCommands->getBranchRemoteListing();
        
        $form = $this->createNewBranchForm($this->project,'project_branch_remote_checkout');
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
     * @Route("/checkout-remote/{id}", name="project_branch_remote_checkout")
     * @Method("POST")
     * @Template("VersionContolGitControlBundle:ProjectBranch:remoteBranches.html.twig")
     */
    public function checkoutRemoteBranchAction(Request $request,$id)
    {

        $this->initAction($id);
        
        $branchName = $this->gitBranchCommands->getCurrentBranch();
         $gitRemoteBranches = $this->gitBranchCommands->getBranchRemoteListing();

        $form = $this->createNewBranchForm($this->project,'project_branch_remote_checkout');
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
     * @Route("/fetchall/{id}/", name="project_branch_fetchall")
     * @Method("GET")
     * @Template("VersionContolGitControlBundle:ProjectBranch:remoteBranches.html.twig")
     */
    public function fetchAllAction($id){
        
        $this->initAction($id);
        
        $response = $this->gitBranchCommands->fetchAll();
        
        $this->get('session')->getFlashBag()->add('notice', $response);
        
        return $this->redirect($this->generateUrl('project_branch_remotes', array('id' => $id)));
    }
    
    
    /**
     * Pulls git repository from remote to local.
     *
     * @Route("/deletebranch/{id}/{branchName}", name="project_deletebranch" , requirements={"branchName"=".+"})
     * @Method("GET")
     * @Template("VersionContolGitControlBundle:Project:branches.html.twig")
     */
    public function deleteBranchAction($id, $branchName){
        
        $this->initAction($id);
        
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
    private function createNewBranchForm($project,$formAction = 'project_branch')
    {

        $defaultData = array();
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
     * @Route("/mergebranch/{id}/{branchName}", name="project_mergebranch" , requirements={"branchName"=".+"})
     * @Method("GET")
     * @Template("VersionContolGitControlBundle:Project:branches.html.twig")
     */
    public function mergeBranchAction($id,$branchName){
        
        $this->initAction($id);
        
        $response = $this->gitBranchCommands->mergeBranch($branchName);
            
        $this->get('session')->getFlashBag()->add('notice', $response);
            
        return $this->redirect($this->generateUrl('project_branches', array('id' => $id)));

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
    protected function initAction($id){
 
        $em = $this->getDoctrine()->getManager();

        $this->project= $em->getRepository('VersionContolGitControlBundle:Project')->find($id);

        if (!$this->project) {
            throw $this->createNotFoundException('Unable to find Project entity.');
        }
        $this->checkProjectAuthorization($this->project,'EDIT');
        
        $this->gitCommands = $this->get('version_control.git_command')->setProject($this->project);
        $this->gitBranchCommands = $this->get('version_control.git_branch')->setProject($this->project);
        
        $this->viewVariables = array_merge($this->viewVariables, array(
            'project'      => $this->project,
            'branchName' => $this->gitBranchCommands->getCurrentBranch(),
            ));
    }
    
    protected function initListingView(){
        
        $branchName = $this->gitBranchCommands->getCurrentBranch();
        //Local Server choice 
        $gitLocalBranches = $this->gitBranchCommands->getBranches(true);
        
        $gitLogs = $this->gitCommands->getLog(1,$branchName);

        $this->viewVariables = array_merge($this->viewVariables, array(
            'branches' => $gitLocalBranches,
            'gitLogs' => $gitLogs
        ));
    }
    
    
}

