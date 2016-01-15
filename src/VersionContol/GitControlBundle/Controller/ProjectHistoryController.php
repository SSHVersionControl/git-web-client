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
     * Displays the project commit history for the current branch.
     *
     * @Route("/{id}", name="project_log")
     * @Method("GET")
     * @Template()
     */
    public function listAction(Request $request,$id)
    {
        $em = $this->getDoctrine()->getManager();

        $project= $em->getRepository('VersionContolGitControlBundle:Project')->find($id);

        if (!$project) {
            throw $this->createNotFoundException('Unable to find Project entity.');
        }
        
        $this->checkProjectAuthorization($project,'VIEW');
        
        $currentPage = $request->query->get('page', 1); 
        
        $gitLogCommand = $this->get('version_control.git_log')->setProject($project);
        $branchName = $gitLogCommand->getCurrentBranch();
 
        $gitLogCommand->setBranch($branchName)
                ->setPage(($currentPage-1));
        
        //Search
        $keyword = $request->query->get('keyword', false);
        $filter= $request->query->get('filter', false);
        if($keyword !== false && trim($keyword) !== ''){
            if($filter !== false){
                if($filter === 'author'){
                    $gitLogCommand->setFilterByAuthor($keyword);
                }elseif($filter === 'content'){
                    $gitLogCommand->setFilterByContent($keyword);
                }else{
                    $gitLogCommand->setFilterByMessage($keyword);
                }
            }
        }
        //print_r($gitLogCommand->getCommand());
        $gitLogs = $gitLogCommand->execute()->getResults();
        
        
 
        return array(
            'project'      => $project,
            'branchName' => $branchName,
            'gitLogs' => $gitLogs,
            'totalCount' => $gitLogCommand->getTotalCount(),
            'limit' => $gitLogCommand->getLimit(),
            'currentPage' => $gitLogCommand->getPage()+1,
            'keyword' => $keyword,
            'filter' => $filter,
        );
    }
}