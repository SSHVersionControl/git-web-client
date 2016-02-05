<?php

namespace VersionContol\GitControlBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
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

/**
 * Project controller.
 *
 * @Route("/project/undo")
 */
class UndoCommitController extends BaseProjectController
{

    protected $gitCommands;
    
    /**
     * Action to do a soft undo on the last commit. This will 
     * allow you to fix any messages in the last commit. This 
     * will not effect any files.
     *
     * @Route("softcommit/{id}", name="undocommit_soft")
     * @Method("GET")
     * @Template("VersionContolGitControlBundle:Error:request.html.twig")
     */
    public function undoSoftCommitAction($id, Request $request){
        $this->initAction($id);

        $response = $this->gitCommands->undoCommit();
        $response .= ' If you pushed the last commit to a remote server you will have to pull from remote before it will allow you to push again.';
        $this->get('session')->getFlashBag()->add('notice', $response);
        
        return $this->redirect($this->generateUrl('project_commitlist', array('id' => $this->project->getId())));
    }
    
    /**
     * Action to do a hard undo on the last commit. 
     *
     * @Route("hardcommit/{id}", name="undocommit_hard")
     * @Method("GET")
     * @Template("VersionContolGitControlBundle:Error:request.html.twig")
     */
    public function undoHardCommitAction($id, Request $request){
        $this->initAction($id);
        
        $response = $this->gitCommands->undoCommitHard();
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
     * @Route("checkoutCommit/{id}/{commitHash}", name="project_checkout_commit")
     * @Method("GET")
     * @Template("VersionContolGitControlBundle:Error:request.html.twig")
     */
    public function checkoutCommitAction($id,$commitHash){
        $this->initAction($id);

        $response = $this->gitCommands->checkoutCommit($commitHash);
        
        $this->get('session')->getFlashBag()->add('notice', $response);
        
        return $this->redirect($this->generateUrl('project_log', array('id' => $this->project->getId())));
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
        $this->checkProjectAuthorization($this->project,'OPERATOR');
        
        $this->gitCommands = $this->get('version_control.git_undo')->setProject($this->project);

    }
    
}

