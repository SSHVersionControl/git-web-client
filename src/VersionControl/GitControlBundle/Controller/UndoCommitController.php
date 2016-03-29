<?php

namespace VersionControl\GitControlBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use VersionControl\GitControlBundle\Controller\Base\BaseProjectController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use VersionControl\GitControlBundle\Entity\Project;
use VersionControl\GitControlBundle\Form\ProjectType;
use VersionControl\GitControlBundle\Utility\GitCommands;
use Symfony\Component\Validator\Constraints\NotBlank;
use VersionControl\GitControlBundle\Entity\UserProjects;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use VersionControl\GitControlBundle\Annotation\ProjectAccess;

/**
 * Project controller.
 *
 * @Route("/project/{id}/undo")
 */
class UndoCommitController extends BaseProjectController
{

    protected $gitUndoCommands;
    
    protected $projectGrantType = 'OPERATOR';


    /**
     * Action to do a soft undo on the last commit. This will 
     * allow you to fix any messages in the last commit. This 
     * will not effect any files.
     *
     * @Route("/softcommit/", name="undocommit_soft")
     * @Method("GET")
     * @Template("VersionControlGitControlBundle:Error:request.html.twig")
     * @ProjectAccess(grantType="EDIT")
     */
    public function undoSoftCommitAction($id, Request $request){

        $response = $this->gitUndoCommands->undoCommit();
        $response .= ' If you pushed the last commit to a remote server you will have to pull from remote before it will allow you to push again.';
        $this->get('session')->getFlashBag()->add('notice', $response);
        
        return $this->redirect($this->generateUrl('project_commitlist', array('id' => $this->project->getId())));
    }
    
    /**
     * Action to do a hard undo on the last commit. 
     *
     * @Route("/hardcommit/", name="undocommit_hard")
     * @Method("GET")
     * @Template("VersionControlGitControlBundle:Error:request.html.twig")
     * @ProjectAccess(grantType="MASTER")
     */
    public function undoHardCommitAction($id, Request $request){    
        
        $response = $this->gitUndoCommands->undoCommitHard();
        $response .= ' If you pushed the last commit to a remote server you will have to pull from remote before it will allow you to push again.';
        $this->get('session')->getFlashBag()->add('notice', $response);
        
        return $this->redirect($this->generateUrl('project_commitlist', array('id' => $this->project->getId())));
    }
    
    /**
     * Action to checkout a commit. All files in the working directory will be
     * updated to match the specified commit. This will put the repository
     *  in a detached HEAD state. Checking out an old commit is a read-only operation. 
     * It’s impossible to harm your repository while viewing an old revision.
     *
     * @Route("/checkoutCommit/{commitHash}", name="project_checkout_commit")
     * @Method("GET")
     * @Template("VersionControlGitControlBundle:Error:request.html.twig")
     * @ProjectAccess(grantType="MASTER")
     */
    public function checkoutCommitAction($id,$commitHash){
        

        $response = $this->gitUndoCommands->checkoutCommit($commitHash);
        
        $this->get('session')->getFlashBag()->add('notice', $response);
        
        return $this->redirect($this->generateUrl('project_log', array('id' => $this->project->getId())));
    }
    
    /**
     * 
     * @param integer $id Project Id
     */
    public function initAction($id, $grantType = 'VIEW'){
 
        parent::initAction($id,$grantType);
        
        $this->gitUndoCommands = $this->gitCommands->command('undo');

    }
    
}

