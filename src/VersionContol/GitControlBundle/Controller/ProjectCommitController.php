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
use VersionContol\GitControlBundle\Form\CommitType;
use VersionContol\GitControlBundle\Entity\Commit;
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

       $branchName = $this->gitSyncCommands->getCurrentBranch();
       $files =  $this->gitCommands->getFilesToCommit();
       
       $commitEntity = new Commit();
       $commitEntity->setProject($this->project);
       $commitEntity->setStatusHash($this->gitCommands->getStatusHash());
       
       $commitForm = $this->createCommitForm($commitEntity);
       
        return array(
            'project'      => $this->project,
            'branchName' => $branchName,
            'files' => $files,
            'commit_form' => $commitForm->createView()
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
        
        $commitEntity = new Commit();
        $commitEntity->setProject($this->project);
        $commitForm = $this->createCommitForm($commitEntity);
        $commitForm->handleRequest($request);

        if ($commitForm->isValid()) {
           
            $selectedGitFiles = $commitEntity->getFiles();
     
            $selectedFiles = array();
            foreach($selectedGitFiles as $gitFile){
                $selectedFiles[] = $gitFile->getPath1();
            }

            $this->gitCommands->stageFiles($selectedFiles);
            $this->gitCommands->commit($commitEntity->getComment());

            $this->get('session')->getFlashBag()->add('notice'
                , count($selectedFiles)." files have been committed");

            return $this->redirect($this->generateUrl('project_commitlist', array('id' => $this->project->getId())));
        }
        
        $branchName = $this->gitSyncCommands->getCurrentBranch();
        $files =  $this->gitCommands->getFilesToCommit();
        
        return array(
            'project'      => $this->project,
            'branchName' => $branchName,
            'files' => $files,
            'commit_form' => $commitForm->createView()
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
    
    
    private function createCommitForm($commitEntity){
 
        $fileChoices = $this->gitCommands->getFilesToCommit();
        $form = $this->createForm((new CommitType())->setFileChoices($fileChoices), $commitEntity, array(
            'action' => $this->generateUrl('project_commit', array('id' => $this->project->getId())),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Commit'));

        return $form;

    }
    
    /**
     * Aborts a merge action. Should only be called after a merge.
     *
     * @Route("/about-merge/{id}", name="project_commit_abortmerge")
     * @Method("GET")
     */
    public function abortMergeAction($id){
        
        $this->initAction($id);
        
        $this->gitCommands = $this->get('version_control.git_command')->setProject($this->project);
        
        return $this->redirect($this->generateUrl('project_commitlist', array('id' => $this->project->getId())));
        
    }
    
}
