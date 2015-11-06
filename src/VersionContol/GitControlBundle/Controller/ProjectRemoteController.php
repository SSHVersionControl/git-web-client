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
 * @Route("/project/remote")
 */
class ProjectRemoteController extends BaseProjectController
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
    protected $gitSyncCommands;
    
    /**
     * The current Project
     * @var Project 
     */
    protected $project;

    /**
     * Form to choose which brabch and remote a user will pull.
     * This is just the form. Also see pullToLocal() 
     *
     * @Route("/{id}", name="project_listremote")
     * @Method("GET")
     * @Template()
     */
    public function listAction($id){
        $this->initAction($id);
        $gitRemoteVersions = $this->gitSyncCommands->getRemoteVersions();
        $branchName = $this->gitCommands->getCurrentBranch();
        
        return array(
            'project'      => $this->project,
            'remotes' => $gitRemoteVersions,
            'branchName' => $branchName
        );
    }
    
    /**
     * Form to choose which brabch and remote a user will pull.
     * This is just the form. Also see pullToLocal() 
     *
     * @Route("new/{id}", name="project_newremote")
     * @Method("GET")
     * @Template()
     */
    public function newAction($id){
        $this->initAction($id);
        $remoteForm = $this->createRemoteForm();
        $branchName = $this->gitCommands->getCurrentBranch();
        
        return array(
            'project'      => $this->project,
            'remote_form' => $remoteForm->createView(),
            'branchName' => $branchName
        );
    }
    
    /**
     * Form to choose which brabch and remote a user will pull.
     * This is just the form. Also see pullToLocal() 
     *
     * @Route("delete/{id}/{remote}", name="project_deleteremote")
     * @Method("GET")
     * @Template()
     */
     public function deleteAction(Request $request,$id,$remote){
        $this->initAction($id);
         
        $response = $this->gitSyncCommands->deleteRemote($remote);
            
        $this->get('session')->getFlashBag()->add('notice', $response);
            
        return $this->redirect($this->generateUrl('project_listremote', array('id' => $id)));
     }
     
     /**
     * Form to choose which brabch and remote a user will pull.
     * This is just the form. Also see pullToLocal() 
     *
     * @Route("rename/{id}", name="project_renameremote")
     * @Method("GET")
     * @Template()
     */
     public function renameAction(Request $request,$id){
         $this->initAction($id);
     }
    
    /**
     * Form to choose which brabch and remote a user will pull.
     * This is just the form. Also see pullToLocal() 
     *
     * @Route("create/{id}", name="project_createremote")
     * @Method("POST")
     * @Template("VersionContolGitControlBundle:ProjectRemote:new.html.twig")
     */
    public function createAction(Request $request,$id)
    {
        $this->initAction($id);

        $addRemoteForm = $this->createRemoteForm(); 
        $addRemoteForm->handleRequest($request);

        if ($addRemoteForm->isValid()) {
            $data = $addRemoteForm->getData();
            $remote = $data['remoteName'];
            $url = $data['remoteUrl'];
            
            
            //Remote Server choice 
            $response = $this->gitSyncCommands->addRemote($remote,$url);
            
            $this->get('session')->getFlashBag()->add('notice', $response);
            
            return $this->redirect($this->generateUrl('project_listremote', array('id' => $id)));
        }
        
        
        return array(
            'project'      => $this->project,
            'pull_form' => $addRemoteForm->createView(),
            'diffs' => array()
        );
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
        $this->gitSyncCommands = $this->get('version_control.git_sync')->setProject($this->project);
    }
    
    
    private function createRemoteForm(){
        $defaultData = array();
        
        $form = $this->createFormBuilder($defaultData, array(
            'action' => $this->generateUrl('project_createremote', array('id' => $this->project->getId())),
            'method' => 'POST',
        ))
        ->add('remoteName', 'text', array(
            'label' => 'Remote Name'
            ,'required' => false
            ,'constraints' => array(
                new NotBlank()
            ))
        )
        ->add('remoteUrl', 'text', array(
            'label' => 'Remote Url'
            ,'required' => false
            ,'constraints' => array(
                new NotBlank()
            ))
        )->add('submit', 'submit', array('label' => 'Add'))
          
        ->getForm();

        return $form;
    }
    
}
