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
    protected $gitCommitCommand;
    
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
       $files =  $this->gitCommitCommand->getFilesToCommit();
       
       $commitEntity = new Commit();
       $commitEntity->setProject($this->project);
       $commitEntity->setStatusHash($this->gitCommitCommand->getStatusHash());
       
       $commitForm = $this->createCommitForm($commitEntity,$files);
       
       
        return array(
            'project'      => $this->project,
            'branchName' => $branchName,
            'files' => $files,
            'commit_form' => $commitForm->createView(),
            'issueCount' => $this->issuesCount
        );
    }

    
    /**
     * Handles the commit form
     *
     * @Route("/{id}", name="project_commit")
     * @Method("POST")
     * @Template("VersionContolGitControlBundle:ProjectCommit:list.html.twig")
     */
    public function commitAction(Request $request,$id)
    {
        $this->initAction($id);
        $files =  $this->gitCommitCommand->getFilesToCommit();
        
        $commitEntity = new Commit();
        $commitEntity->setProject($this->project);
        $commitForm = $this->createCommitForm($commitEntity,$files);
        $commitForm->handleRequest($request);

        if ($commitForm->isValid()) {
           
            $selectedGitFiles = $commitEntity->getFiles();
     
            try{
                $selectedFiles = array();
                $filesCommited = 0;
                
                if(is_array($selectedGitFiles)){
                    foreach($selectedGitFiles as $gitFile){
                        $selectedFiles[] = $gitFile->getPath1();
                    }
                
                    $filesCommited = count($selectedFiles);
                    //Git Stage selected files
                    $this->gitCommitCommand->stageFiles($selectedFiles);
                }else{
                    //To many files
                    if($selectedGitFiles === true){
                         $this->gitCommitCommand->stageAll();
                         
                         $filesCommited = count($files);
                    }
                }

                //Handle Issue Action eg Close issue. Update Commit message
                $this->handleIssue($commitEntity);
     
                //Git Commit 
                $this->gitCommitCommand->commit($commitEntity->getComment());
                
                //Set notice of successfull commit
                $this->get('session')->getFlashBag()->add('notice'
                , $filesCommited." files have been committed");
                
                //Git Push to remote repository
                $this->pushToRemote($commitEntity);
              
                return $this->redirect($this->generateUrl('project_commitlist', array('id' => $this->project->getId())));
        
            }catch(\Exception $e){
                $this->get('session')->getFlashBag()->add('error'
                , $e->getMessage());
            }

        }
        
        $branchName = $this->gitSyncCommands->getCurrentBranch();
        
        
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
        
        $this->gitCommitCommand = $this->get('version_control.git_commit')->setProject($this->project);
        $this->gitSyncCommands = $this->get('version_control.git_sync')->setProject($this->project);

        $this->issuesCount = $em->getRepository('VersionContolGitControlBundle:Issue')->countIssuesForProjectWithStatus($this->project,'open');
        
        $this->branchName = $this->gitCommitCommand->getCurrentBranch();
        
        $this->viewVariables = array_merge($this->viewVariables, array(
            'project'      => $this->project,
            'branchName' => $this->branchName,
            ));
    }
    
    
    private function createCommitForm($commitEntity,$fileChoices){
 
        $includeIssues = ($this->issuesCount > 0)?true:false;
        //$fileChoices = $this->gitCommitCommand->getFilesToCommit();
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
        
        $this->gitCommitCommand = $this->get('version_control.git_command')->setProject($this->project);
        
        return $this->redirect($this->generateUrl('project_commitlist', array('id' => $this->project->getId())));
        
    }
    
    /**
     * Check if issue options have been set and updates git message 
     * and closes issue if certain issue actions are set.
     * 
     * @param Commit $commitEntity]
     */
    protected function handleIssue(\VersionContol\GitControlBundle\Entity\Commit &$commitEntity){
        $issueId = $commitEntity->getIssue();
        $commitMessage = $commitEntity->getComment();
        $issueCloseStatus = array('Fixed','Closed','Resolved');
         
        if($issueId){
            $em = $this->getDoctrine()->getManager();
            $issueEntity = $em->getRepository('VersionContolGitControlBundle:Issue')->find($issueId);
            if($issueEntity){
                $issueAction = $commitEntity->getIssueAction();
                $commitMessage = $issueAction.' #'.$issueEntity->getId().':'.$commitMessage;
                $commitEntity->setComment($commitMessage);
                if(in_array($issueAction,$issueCloseStatus)){
                    //Close Issue
                    $this->closeIssue($issueEntity);
                }
            }
        }
    } 
    
    /**
     * Closes Issue
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
    
    
    /**
     * Push to remote repositories. Supports mulitple pushes
     * 
     * @param Commit $commitEntity
     */
    protected function pushToRemote(\VersionContol\GitControlBundle\Entity\Commit $commitEntity){
        $branch = $this->gitSyncCommands->getCurrentBranch();
        
        $gitRemotes = $commitEntity->getPushRemote();
        if(count($gitRemotes) > 0){

            foreach($gitRemotes as $gitRemote){
                $response = $this->gitSyncCommands->push($gitRemote,$branch);  
                $this->get('session')->getFlashBag()->add('notice', $response);
            }
        }
    }
    
    /**
     * Show Git commit diff
     *
     * @Route("/filediff/{id}/{difffile}", name="project_filediff")
     * @Method("GET")
     * @Template()
     */
    public function fileDiffAction($id,$difffile){
        
        $this->initAction($id);
        
        $gitDiffCommand = $this->get('version_control.git_diff')->setProject($this->project);

        $difffile = urldecode($difffile);
       
        $gitDiffs = $gitDiffCommand->getDiffFile($difffile);
   
        return array_merge($this->viewVariables, array(
            'diffs' => $gitDiffs,
        ));
    }
    
}
