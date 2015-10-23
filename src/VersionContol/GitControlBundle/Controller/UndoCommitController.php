<?php

namespace VersionContol\GitControlBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
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
class UndoCommitController extends Controller
{

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
        $em = $this->getDoctrine()->getManager();

        $project= $em->getRepository('VersionContolGitControlBundle:Project')->find($id);

        if (!$project) {
            throw $this->createNotFoundException('Unable to find Project entity.');
        }
        
        $this->checkProjectAuthorization($project,'OPERATOR');

        $gitCommands = $this->get('version_control.git_command')->setProject($project);
        $response = $gitCommands->undoCommit();
        $response .= ' If you pushed the last commit to a remote server you will have to pull from remote before it will allow you to push again.';
        $this->get('session')->getFlashBag()->add('notice', $response);
        
        return $this->redirect($this->generateUrl('project_show', array('id' => $project->getId())));
    }
    
    /**
     * Action to do a hard undo on the last commit. 
     *
     * @Route("hardcommit/{id}", name="undocommit_hard")
     * @Method("GET")
     * @Template("VersionContolGitControlBundle:Error:request.html.twig")
     */
    public function undoHardCommitAction($id, Request $request){
        $em = $this->getDoctrine()->getManager();

        $project= $em->getRepository('VersionContolGitControlBundle:Project')->find($id);

        if (!$project) {
            throw $this->createNotFoundException('Unable to find Project entity.');
        }
        
        $this->checkProjectAuthorization($project,'OPERATOR');

        $gitCommands = $this->get('version_control.git_command')->setProject($project);
        $response = $gitCommands->undoCommit();
        $response .= ' If you pushed the last commit to a remote server you will have to pull from remote before it will allow you to push again.';
        $this->get('session')->getFlashBag()->add('notice', $response);
        
        return $this->redirect($this->generateUrl('project_show', array('id' => $project->getId())));
    }
    
    /**
     * 
     * @param VersionContol\GitControlBundle\Entity\Project $project
     * @throws AccessDeniedException
     */
    protected function checkProjectAuthorization(\VersionContol\GitControlBundle\Entity\Project $project,$grantType='MASTER'){
        $authorizationChecker = $this->get('security.authorization_checker');

        // check for edit access
        if (false === $authorizationChecker->isGranted($grantType, $project)) {
            throw new AccessDeniedException();
        }
    }
}

