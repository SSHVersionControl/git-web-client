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

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;

/**
 * Project controller.
 *
 * @Route("/history")
 */
class ProjectHistoryController extends BaseProjectController
{
    /**
     *
     * @var GitCommand 
     */
    protected $gitLogCommand;
    
    /**
     *
     * @var GitCommand 
     */
    protected $gitBranchCommands;
    
    /**
     * Displays the project commit history for the current branch.
     *
     * @Route("/{id}", name="project_log")
     * @Method("GET")
     * @Template()
     */
    public function listAction(Request $request,$id)
    {
        $this->initAction($id);
        
        $currentPage = $request->query->get('page', 1); 
 
        $this->gitLogCommand->setBranch($this->branchName)
                ->setPage(($currentPage-1));
        
        //Search
        $keyword = $request->query->get('keyword', false);
        $filter= $request->query->get('filter', false);
        if($keyword !== false && trim($keyword) !== ''){
            if($filter !== false){
                if($filter === 'author'){
                    $this->gitLogCommand->setFilterByAuthor($keyword);
                }elseif($filter === 'content'){
                    $this->gitLogCommand->setFilterByContent($keyword);
                }else{
                    $this->gitLogCommand->setFilterByMessage($keyword);
                }
            }
        }

        $gitLogs = $this->gitLogCommand->execute()->getResults();
        
        
 
        return array_merge($this->viewVariables, array(

            'gitLogs' => $gitLogs,
            'totalCount' => $this->gitLogCommand->getTotalCount(),
            'limit' => $this->gitLogCommand->getLimit(),
            'currentPage' => $this->gitLogCommand->getPage()+1,
            'keyword' => $keyword,
            'filter' => $filter,
        ));
    }
    
    /**
     * Show Git commit diff
     *
     * @Route("/commitdiff/{id}/{commitHash}", name="project_commitdiff")
     * @Method("GET")
     * @Template()
     */
    public function commitDiffAction($id,$commitHash){
        
        $this->initAction($id);
        
        $gitDiffCommand = $this->get('version_control.git_diff')->setProject($this->project);

        $this->gitLogCommand
                ->setLogCount(1)
                ->setCommitHash($commitHash);
        
        //$gitLog = $this->gitFilesCommands->getCommitLog($commitHash,$this->branchName);
        $gitLog = $this->gitLogCommand->execute()->getFirstResult();
        
        //Get git Diff
        $gitDiffs = $gitDiffCommand->getCommitDiff($commitHash);
        
        return array_merge($this->viewVariables, array(
            'log' => $gitLog,
            'diffs' => $gitDiffs,
        ));
    }
    
    /**
     * 
     * @param integer $id Project Id
     */
    protected function initAction($id){
 
        $em = $this->getDoctrine()->getManager();

        $this->project= $em->getRepository('VersionContolGitControlBundle:Project')->find($id);

        if (!$this->project) {
            throw $this->createNotFoundException('Unable to find Project entity.');
        }
        $this->checkProjectAuthorization($this->project,'VIEW');
        
        $this->gitLogCommand = $this->get('version_control.git_log')->setProject($this->project);
        $this->gitBranchCommands = $this->get('version_control.git_branch')->setProject($this->project);
        
        $this->branchName = $this->gitBranchCommands->getCurrentBranch();
        
        $this->viewVariables = array_merge($this->viewVariables, array(
            'project'      => $this->project,
            'branchName' => $this->branchName,
            ));
    }
}