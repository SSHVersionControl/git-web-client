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
     * Number of issues for this project
     * @var integer 
     */
    protected $issuesCount;
    
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
            'commit_form' => $commitForm->createView(),
            'issueCount' => $this->issuesCount
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

            try{
                $this->gitCommands->stageFiles($selectedFiles);
                $commitMessage = $commitEntity->getComment();
                
                //Handle Issue
                $issueId = $commitEntity->getIssue();
                
                if($issueId){
                    $em = $this->getDoctrine()->getManager();
                    $issueEntity = $em->getRepository('VersionContolGitControlBundle:Issue')->find($issueId);
                    if($issueEntity){
                        $issueAction = $commitEntity->getIssueAction();
                        $commitMessage = $issueAction.' #'.$issueEntity->getId().':'.$commitMessage;
                        if(in_array($issueAction,array('Fixed','Closed','Resolved'))){
                            //Close Issue
                            $this->closeIssue($issueEntity);
                        }
                    }
                }
                
                $this->gitCommands->commit($commitEntity->getComment());
                
                $this->get('session')->getFlashBag()->add('notice'
                , count($selectedFiles)." files have been committed");
                
                //Push to remote
                $this->pushToRemote($commitEntity);
              
                return $this->redirect($this->generateUrl('project_commitlist', array('id' => $this->project->getId())));
        
            }catch(\Exception $e){
                $this->get('session')->getFlashBag()->add('error'
                , $e->getMessage());
            }

        }
        
        $branchName = $this->gitSyncCommands->getCurrentBranch();
        $files =  $this->gitCommands->getFilesToCommit();
        
        return array(
            'project'      => $this->project,
            'branchName' => $branchName,
            'files' => $files,
            'commit_form' => $commitForm->createView(),
            'issueCount' => $this->issuesCount
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

        $this->issuesCount = $em->getRepository('VersionContolGitControlBundle:Issue')->countIssuesForProjectWithStatus($this->project,'open');
    }
    
    
    private function createCommitForm($commitEntity){
 
        $includeIssues = ($this->issuesCount > 0)?true:false;
        $fileChoices = $this->gitCommands->getFilesToCommit();
        $gitRemoteVersions = $this->gitSyncCommands->getRemoteVersions();
        
        $form = $this->createForm((new CommitType($includeIssues,$gitRemoteVersions))->setFileChoices($fileChoices), $commitEntity, array(
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
    
    /**
     * 
     * @param \VersionContol\GitControlBundle\Entity\Issue $issueEntity
     * @throws \Exception
     */
    protected function closeIssue(\VersionContol\GitControlBundle\Entity\Issue $issueEntity){
        $em = $this->getDoctrine()->getManager();

        if ($issueEntity->getProject()->getId() !== $this->project->getId()) {
            throw $this->createNotFoundException('Issue does not match this project. Issue state was not updated');
        }
        
        $issueEntity->setClosed();
        $em->flush();
        
        $this->get('session')->getFlashBag()->add('notice'
                ,"Issue #".$issueEntity->getId()." has been closed");
    }
    
    protected function pushToRemote($commitEntity){
        $branch = $this->gitSyncCommands->getCurrentBranch();
        
        $gitRemotes = $commitEntity->getPushRemote();
        if(count($gitRemotes) > 0){
            //print_r($gitRemotes);
            foreach($gitRemotes as $gitRemote){
                $response = $this->gitSyncCommands->push($gitRemote,$branch);  
                $this->get('session')->getFlashBag()->add('notice', $response);
            }
        }
    }
    
}
