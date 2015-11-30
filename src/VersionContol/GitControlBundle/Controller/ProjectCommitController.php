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
 * Project Commit controller.
 *
 * @Route("/project/commit")
 */
class ProjectCommitController extends BaseProjectController
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
     * List files to be commited.
     *
     * @Route("/{id}", name="project_commitlist")
     * @Method("GET")
     * @Template()
     */
    public function listAction($id)
    {
        
       $this->initAction($id);

       $branchName = $this->gitCommands->getCurrentBranch();

       $files =  $this->gitCommands->getFilesToCommit();
       $statusHash = $this->gitCommands->getStatusHash();
       
        return array(
            'project'      => $this->project,
            'branchName' => $branchName,
            'files' => $files,
            'statusHash' => $statusHash
        );
    }

    
    /**
     * Creates a new Project entity.
     *
     * @Route("/{id}", name="project_commit")
     * @Method("POST")
     * @Template("VersionContolGitControlBundle:ProjectCommit:list.html.twig")
     */
    public function commitAction(Request $request,$id)
    {
        $this->initAction($id);
        
        $comment = $this->get('request')->request->get('comment');
        if(!trim($comment)){
            throw $this->createNotFoundException('Please add comment');
        }
        
        $selectedFiles = $this->get('request')->request->get('files');
        if($selectedFiles && is_array($selectedFiles) && ($selectedFiles) > 0){

            //Check if the 
            $gitStatusHash = $this->gitCommands->getStatusHash();
            $statusHash = $this->get('request')->request->get('statushash');
            if($gitStatusHash !== $statusHash){
                throw new \Exception('The git status has changed. Please refresh the page and retry the commit');
            }
            //$gitCommands = new GitCommands($gitPath);
            $this->gitCommands->stageFiles($selectedFiles);
            
            $this->gitCommands->commit($comment);
            
            $this->get('session')->getFlashBag()->add('notice'
                , count($selectedFiles)." files have been committed");
        }else{
            //Error need to select at least on file
        }
        
        return $this->redirect($this->generateUrl('project_commitlist', array('id' => $this->project->getId())));

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
