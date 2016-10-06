<?php
/*
 * This file is part of the GitControlBundle package.
 *
 * (c) Paul Schweppe <paulschweppe@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace VersionControl\GitControlBundle\Controller;

use VersionControl\GitControlBundle\Controller\Base\BaseProjectController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use VersionControl\GitControlBundle\Entity\Project;
use VersionControl\GitControlBundle\Form\ProjectType;

use Symfony\Component\Validator\Constraints\NotBlank;
use VersionControl\GitControlBundle\Entity\UserProjects;
use VersionControl\GitCommandBundle\GitCommands\GitCommand;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
 /** ///Route("/example", service="example_bundle.controller.example_controller") */
use VersionControl\GitControlBundle\Annotation\ProjectAccess;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
/**
 * Project controller.
 *
 * @Route("/project/{id}/remote")
 */
class ProjectRemoteController extends BaseProjectController
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
    
    protected $projectGrantType = 'EDIT';

    /**
     * Form to choose which brabch and remote a user will pull.
     * This is just the form. Also see pullToLocal() 
     *
     * @Route("/", name="project_listremote")
     * @Method("GET")
     * @Template()
     * @ProjectAccess(grantType="MASTER")
     */
    public function listAction($id){
        $gitRemoteVersions = array();
        try{
            $gitRemoteVersions = $this->gitSyncCommands->getRemoteVersions();
        }catch (\Exception $e) {
            $this->get('session')->getFlashBag()->add('error', $e->getMessage());
        }
        return array_merge($this->viewVariables, array(
            'remotes' => $gitRemoteVersions,
            'branchName' => $this->branchName
        ));
    }
    
    /**
     * Form to choose which brabch and remote a user will pull.
     * This is just the form. Also see pullToLocal() 
     *
     * @Route("/new/", name="project_newremote")
     * @Method("GET")
     * @Template()
     * @ProjectAccess(grantType="MASTER")
     */
    public function newAction($id){
        
        $remoteForm = $this->createRemoteForm();
        
        
        return array_merge($this->viewVariables, array(
            'remote_form' => $remoteForm->createView(),
            'branchName' => $this->branchName
        ));
    }
    

    /**
     * Form to choose which brabch and remote a user will pull.
     * This is just the form. Also see pullToLocal() 
     *
     * @Route("/create/", name="project_createremote")
     * @Method("POST")
     * @Template("VersionControlGitControlBundle:ProjectRemote:new.html.twig")
     * @ProjectAccess(grantType="MASTER")
     */
    public function createAction(Request $request,$id)
    {
        

        $addRemoteForm = $this->createRemoteForm(); 
        $addRemoteForm->handleRequest($request);

        if ($addRemoteForm->isValid()) {
            $data = $addRemoteForm->getData();
            $remote = $data['remoteName'];
            $url = $data['remoteUrl'];

            try{
                //Remote Server choice 
                $response = $this->gitSyncCommands->addRemote($remote,$url);
                $this->get('session')->getFlashBag()->add('notice', $response);
            }catch (\Exception $e) {
                $this->get('session')->getFlashBag()->add('error', $e->getMessage());
            }
            return $this->redirect($this->generateUrl('project_listremote', array('id' => $id)));
        }
        
        
        return array_merge($this->viewVariables, array(
            'pull_form' => $addRemoteForm->createView(),
            'diffs' => array()
        ));
    }
    
    /**
     * Form to choose which brabch and remote a user will pull.
     * This is just the form. Also see pullToLocal() 
     *
     * @Route("/delete/{remote}", name="project_deleteremote")
     * @Method("GET")
     * @Template()
     * @ProjectAccess(grantType="MASTER")
     */
     public function deleteAction(Request $request,$id,$remote){
        
        try{
            $response = $this->gitSyncCommands->deleteRemote($remote);
            $this->get('session')->getFlashBag()->add('notice', $response);
        }catch (\Exception $e) {
            $this->get('session')->getFlashBag()->add('error', $e->getMessage());
        }    
        return $this->redirect($this->generateUrl('project_listremote', array('id' => $id)));
     }
     
     
    /**
     * Create rename remote form. 
     *
     * @Route("/rename/{remote}", name="project_renameremote")
     * @Method("GET")
     * @Template()
     * @ProjectAccess(grantType="MASTER")
     */
     public function renameAction(Request $request,$id,$remote){
        
         
        $defaultData = array('remoteName' => $remote);
        $renameRemoteForm = $this->createRenameRemoteForm($defaultData);
        
        return array_merge($this->viewVariables, array(
            'remote_form' => $renameRemoteForm->createView(),
            'branchName' => $this->branchName
        ));
                
     }
     
    /**
     * Changes the name of the remote repositiory in git for the local branch
     *
     * @Route("/rename/{remote}", name="project_remoteupdate")
     * @Method("POST")
     * @Template("VersionControlGitControlBundle:ProjectRemote:rename.html.twig")
     * @ProjectAccess(grantType="MASTER")
     */
     public function updateAction(Request $request,$id){
         
        $renameRemoteForm = $this->createRenameRemoteForm();
        
        $renameRemoteForm->handleRequest($request);

        if ($renameRemoteForm->isValid()) {
            $data = $renameRemoteForm->getData();
            $remoteName = $data['remoteName'];
            $newRemoteName = $data['newRemoteName'];
            
            try{
                $response = $this->gitSyncCommands->renameRemote($remoteName,$newRemoteName);
                $this->get('session')->getFlashBag()->add('notice',$response);
            }catch (\Exception $e) {
                $this->get('session')->getFlashBag()->add('error', $e->getMessage());
            }

            return $this->redirect($this->generateUrl('project_listremote', array('id' => $id)));
        }

        
        return array_merge($this->viewVariables, array(
            'remote_form' => $renameRemoteForm->createView(),
            'branchName' => $this->branchName
        ));
                
     }
    
    
    
    /**
     * 
     * @param integer $id
     */
    public function initAction($id, $grantType = 'VIEW'){
        
        $redirectUrl = parent::initAction($id,$grantType);
        if($redirectUrl){
            return $redirectUrl;
        }
        $this->gitSyncCommands = $this->gitCommands->command('sync');

    }
    
    
    private function createRemoteForm(){
        $defaultData = array();
        
        $form = $this->createFormBuilder($defaultData, array(
            'action' => $this->generateUrl('project_createremote', array('id' => $this->project->getId())),
            'method' => 'POST',
        ))
        ->add('remoteName', TextType::class, array(
            'label' => 'Remote Name'
            ,'required' => false
            ,'constraints' => array(
                new NotBlank()
            ))
        )
        ->add('remoteUrl', TextType::class, array(
            'label' => 'Remote Url'
            ,'required' => false
            ,'constraints' => array(
                new NotBlank()
            ))
        )->add('submit', SubmitType::class, array('label' => 'Add'))
          
        ->getForm();

        return $form;
    }
    
    private function createRenameRemoteForm($defaultData = array()){

        
        $form = $this->createFormBuilder($defaultData, array(
            'action' => $this->generateUrl('project_createremote', array('id' => $this->project->getId())),
            'method' => 'POST',
        ))
        ->add('remoteName', HiddenType::class, array(
            'constraints' => array(
                new NotBlank()
            ))
        )
        ->add('newRemoteName', TextType::class, array(
            'label' => 'New Remote Name'
            ,'required' => false
            ,'constraints' => array(
                new NotBlank()
            ))
        )->add('submit', SubmitType::class, array('label' => 'Rename'))
          
        ->getForm();

        return $form;
    }
    
}
