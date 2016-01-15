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
 * Project controller.
 *
 * @Route("/sync/")
 */
class ProjectSyncController extends BaseProjectController
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
     * Finds and displays a Project entity.
     *
     * @Route("push/{id}", name="project_push")
     * @Method("GET")
     * @Template()
     */
    public function pushAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $project= $em->getRepository('VersionContolGitControlBundle:Project')->find($id);

        if (!$project) {
            throw $this->createNotFoundException('Unable to find Project entity.');
        }
        
        $this->checkProjectAuthorization($project,'EDIT');

        $this->initAction($project);
        
        //Remote Server choice 
        $gitRemoteVersions = $this->gitSyncCommands->getRemoteVersions();
        
        $pushForm = $this->createPushPullForm($project);
        $pushForm->add('push', 'submit', array('label' => 'Push'));

        return array(
            'project'      => $project,
            'remoteVersions' => $gitRemoteVersions,
            'push_form' => $pushForm->createView()
        );
    }
    
    /**
     * Finds and displays a Project entity.
     *
     * @Route("pushremote/{id}", name="project_pushremote")
     * @Method("POST")
     * @Template("VersionContolGitControlBundle:ProjectSync:push.html.twig")
     */
    public function pushToRemoteAction(Request $request,$id)
    {
        $em = $this->getDoctrine()->getManager();

        $project= $em->getRepository('VersionContolGitControlBundle:Project')->find($id);

        if (!$project) {
            throw $this->createNotFoundException('Unable to find Project entity.');
        }
        
        $this->checkProjectAuthorization($project,'EDIT');

        $this->initAction($project);
         
       
        $gitRemoteVersions = $this->gitSyncCommands->getRemoteVersions();
        
        $pushForm = $this->createPushPullForm($project);
        $pushForm->add('push', 'submit', array('label' => 'Push'));
        $pushForm->handleRequest($request);

        if ($pushForm->isValid()) {
            $data = $pushForm->getData();
            $remote = $data['remote'];
            $branch = $data['branch'];
            $response = $this->gitSyncCommands->push($remote,$branch);
            
            $this->get('session')->getFlashBag()->add('notice', $response);
            
            return $this->redirect($this->generateUrl('project_push', array('id' => $id)));
        }

        return array(
            'project'      => $project,
            'remoteVersions' => $gitRemoteVersions,
            'push_form' => $pushForm->createView()
        );
    }

    
    /**
     * Form to choose which brabch and remote a user will pull.
     * This is just the form. Also see pullToLocal() 
     *
     * @Route("pull/{id}", name="project_pull")
     * @Method("GET")
     * @Template()
     */
    public function pullAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $project= $em->getRepository('VersionContolGitControlBundle:Project')->find($id);

        if (!$project) {
            throw $this->createNotFoundException('Unable to find Project entity.');
        }
        $this->checkProjectAuthorization($project,'EDIT');

        $this->initAction($project);
        
        //Remote Server choice 
        $gitRemoteVersions = $this->gitSyncCommands->getRemoteVersions();

        $pullForm = $this->createPushPullForm($project,"project_pulllocal");
        $pullForm->add('pull', 'submit', array('label' => 'Pull'));
        $pullForm->add('viewDiff', 'submit', array('label' => 'View Diff'));
        
        
        return array(
            'project'      => $project,
            'remoteVersions' => $gitRemoteVersions,
            'pull_form' => $pullForm->createView(),
            'diffs' => array()
        );
    }
    
    /**
     * Pulls git repository from remote to local.
     *
     * @Route("pulllocal/{id}", name="project_pulllocal")
     * @Method("POST")
     * @Template("VersionContolGitControlBundle:ProjectSync:pull.html.twig")
     */
    public function pullToLocalAction(Request $request,$id)
    {
        $diffs = array();
        $em = $this->getDoctrine()->getManager();

        $project= $em->getRepository('VersionContolGitControlBundle:Project')->find($id);

        if (!$project) {
            throw $this->createNotFoundException('Unable to find Project entity.');
        }
        $this->checkProjectAuthorization($project,'EDIT');

        $this->initAction($project);

        $gitRemoteVersions = $this->gitSyncCommands->getRemoteVersions();
        
        $pullForm = $this->createPushPullForm($project,"project_pulllocal");
        $pullForm->add('pull', 'submit', array('label' => 'Pull'));
        $pullForm->add('viewDiff', 'submit', array('label' => 'View Diff'));
        $pullForm->handleRequest($request);

        if ($pullForm->isValid()) {
            $data = $pullForm->getData();
            $remote = $data['remote'];
            $branch = $data['branch'];
             //die('form valid');
            if($pullForm->get('viewDiff')->isClicked()){
                $response = $this->gitSyncCommands->fetch($remote,$branch);
                $this->get('session')->getFlashBag()->add('notice', $response);
                $diffs = $this->gitCommands->getDiffRemoteBranch($remote,$branch);
                
            }elseif($pullForm->get('pull')->isClicked()){
                $response = $this->gitSyncCommands->pull($remote,$branch);
                $this->get('session')->getFlashBag()->add('notice', $response);

                return $this->redirect($this->generateUrl('project_pull', array('id' => $id)));
            }
            
        }

        return array(
            'project'      => $project,
            'remoteVersions' => $gitRemoteVersions,
            'pull_form' => $pullForm->createView(),
            'diffs' => $diffs
        );
    }
    

    
    /**
     * 
     * @param Project $project
     */
    protected function initAction(Project $project){
        $this->gitCommands = $this->get('version_control.git_command')->setProject($project);
        $this->gitSyncCommands = $this->get('version_control.git_sync')->setProject($project);
    }
    
    /**
    * Creates a form to edit a Project entity.
    *
    * @param Project $project The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createPushPullForm($project,$formAction = 'project_pushremote')
    {
                //Remote Server choice 
        $gitRemoteVersions = $this->gitSyncCommands->getRemoteVersions();
        $remoteChoices = array();
        foreach($gitRemoteVersions as $remoteVersion){
            $remoteChoices[$remoteVersion[0]] = $remoteVersion[0].'('.$remoteVersion[1].')'; 
        }
        
        //Local Branch choice
        $branches = $this->gitCommands->getLocalBranches();
        $branchChoices = array();
        foreach($branches as $branchName){
            $branchChoices[$branchName] = $branchName;
        }
               
        //Current branch
        $currentBranch = $this->gitCommands->getCurrentBranch();
        
        reset($remoteChoices);
        $firstOrigin = key($remoteChoices);
        
        $defaultData = array('branch' => $currentBranch);
        $form = $this->createFormBuilder($defaultData, array(
                'action' => $this->generateUrl($formAction, array('id' => $project->getId())),
                'method' => 'POST',
            ))
            ->add('remote', 'choice', array(
                'label' => 'Remote Server'
                ,'choices'  => $remoteChoices
                ,'data' => $firstOrigin
                ,'required' => false
                ,'constraints' => array(
                    new NotBlank()
                ))
            )   
            ->add('branch', 'choice', array(
                'label' => 'Branch'
                ,'choices'  => $branchChoices
                ,'preferred_choices' => array($currentBranch)
                ,'data' => trim($currentBranch)
                ,'required' => false
                ,'constraints' => array(
                    new NotBlank()
                ))
            )   
            ->getForm();

        //$form->add('submitMain', 'submit', array('label' => 'Push'));
        return $form;
    }

}
