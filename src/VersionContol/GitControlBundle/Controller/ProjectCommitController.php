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

use VersionContol\GitControlBundle\Annotation\ProjectAccess;
 /** ///Route("/example", service="example_bundle.controller.example_controller") */

/**
 * Project Commit controller.
 *
 * @Route("/project/{id}/commit")
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
     * Issue Respository
     * @var VersionContol\GitControlBundle\Repository\Issues\IssueRepositoryInterface 
     */
    protected $issueRepository;
    
    /**
     * List files to be commited.
     *
     * @Route("/", name="project_commitlist")
     * @Method("GET")
     * @Template()
     * @ProjectAccess(grantType="EDIT")
     */
    public function listAction($id)
    {
        
       $files =  $this->gitCommitCommand->getFilesToCommit();
       
       $commitEntity = new Commit();
       $commitEntity->setProject($this->project);
       $commitEntity->setStatusHash($this->gitCommitCommand->getStatusHash());
       
       $commitForm = $this->createCommitForm($commitEntity,$files);
       
       
        return array_merge($this->viewVariables, array(
            'files' => $files,
            'commit_form' => $commitForm->createView(),
            'issueCount' => $this->issuesCount
        ));
    }

    
    /**
     * Handles the commit form
     *
     * @Route("/", name="project_commit")
     * @Method("POST")
     * @Template("VersionContolGitControlBundle:ProjectCommit:list.html.twig")
     * @ProjectAccess(grantType="EDIT")
     */
    public function commitAction(Request $request)
    {
   
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
              
                return $this->redirect($this->generateUrl('project_commitlist'));
        
            }catch(\Exception $e){
                $this->get('session')->getFlashBag()->add('error'
                , $e->getMessage());
            }

        }
        
        
        
        return array_merge($this->viewVariables, array(
            'files' => $files,
            'commit_form' => $commitForm->createView(),
            'issueCount' => $this->issuesCount
        ));

    } 
    
    /**
     * 
     * @param integer $id
     */
    public function initAction($id, $grantType = 'EDIT'){
 
        parent::initAction($id,$grantType);
        
        $this->gitCommitCommand = $this->gitCommands->command('commit');
        $this->gitSyncCommands = $this->gitCommands->command('sync');
        
        $em = $this->getDoctrine()->getManager();
        
        $issueIntegrator= $em->getRepository('VersionContolGitControlBundle:ProjectIssueIntegrator')->findOneByProject($this->project);
        $this->issueManager = $this->get('version_control.issue_repository_manager');
        if($issueIntegrator){
            $this->issueManager->setIssueIntegrator($issueIntegrator);
        }else{
            $this->issueManager->setProject($this->project);
        }
        $this->issueRepository = $this->issueManager->getIssueRepository();
        $this->issuesCount = $this->issueRepository->countFindIssues('','open');

      
    }
    
    
    private function createCommitForm($commitEntity,$fileChoices){
 
        $includeIssues = ($this->issuesCount > 0)?true:false;
        //$fileChoices = $this->gitCommitCommand->getFilesToCommit();
        $gitRemoteVersions = $this->gitSyncCommands->getRemoteVersions();
        
        $form = $this->createForm((new CommitType($includeIssues,$gitRemoteVersions))->setFileChoices($fileChoices), $commitEntity, array(
            'action' => $this->generateUrl('project_commit'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Commit'));

        return $form;

    }
    
    /**
     * Aborts a merge action. Should only be called after a merge.
     *
     * @Route("/about-merge/", name="project_commit_abortmerge")
     * @Method("GET")
     * @ProjectAccess(grantType="EDIT")
     */
    public function abortMergeAction($id){
        
        //$this->gitCommitCommand = $this->get('version_control.git_command')->setProject($this->project);
        
        return $this->redirect($this->generateUrl('project_commitlist'));
        
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
            $issueEntity = $this->issueRepository->findIssueById($issueId);
            if($issueEntity){
                $issueAction = $commitEntity->getIssueAction();
                $commitMessage = $issueAction.' #'.$issueEntity->getId().':'.$commitMessage;
                $commitEntity->setComment($commitMessage);
                if(in_array($issueAction,$issueCloseStatus)){
                    //Close Issue
                    $this->issueRepository->closeIssue($issueEntity->getId());
                }
            }
        }
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
     * @Route("/filediff/{difffile}", name="project_filediff")
     * @Method("GET")
     * @Template()
     * @ProjectAccess(grantType="EDIT")
     */
    public function fileDiffAction($id,$difffile){
        
        $gitDiffCommand = $this->gitCommands->command('diff');

        $difffile = urldecode($difffile);
       
        $gitDiffs = $gitDiffCommand->getDiffFile($difffile);
   
        return array_merge($this->viewVariables, array(
            'diffs' => $gitDiffs,
        ));
    }
    
}
