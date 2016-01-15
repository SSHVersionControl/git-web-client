<?php

namespace VersionContol\GitControlBundle\Controller;

use VersionContol\GitControlBundle\Controller\Base\BaseProjectController;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use VersionContol\GitControlBundle\Entity\Project;
use VersionContol\GitControlBundle\Entity\ProjectEnvironment;

use VersionContol\GitControlBundle\Form\ProjectEnvironmentType;

use VersionContol\GitControlBundle\Entity\UserProjects;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Project controller.
 *
 * @Route("/project/project-environment")
 */
class ProjectEnvironmentController extends BaseProjectController
{

    /**
     * Lists all Project entities.
     *
     * @Route("s/{project}", name="projectenvironment")
     * @ParamConverter("project", class="VersionContolGitControlBundle:Project")
     * @Method("GET")
     * @Template()
     */
    public function indexAction($project, Request $request)
    {
        $this->checkProjectAuthorization($project);
        
        $em = $this->getDoctrine()->getManager();

        $keyword = $request->query->get('keyword', false);
        
        $query = $em->getRepository('VersionContolGitControlBundle:ProjectEnvironment')->findByProjectAndKeyword($keyword,true)->getQuery();
        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1)/*page number*/,
            15/*limit per page*/
        );

        return array(
            'pagination' => $pagination,
        );
    }
    
    
    /**
     * Creates a new Project entity.
     *
     * @Route("/{project}", name="projectenvironment_create")
     * @ParamConverter("project", class="VersionContolGitControlBundle:Project")
     * @Method("POST")
     * @Template("VersionContolGitControlBundle:ProjectEnvironment:new.html.twig")
     */
    public function createAction($project, Request $request)
    {
        $this->checkProjectAuthorization($project);
         
        $projectEnvironment = new ProjectEnvironment();
        $form = $this->createCreateForm($projectEnvironment,$project);
        $form->handleRequest($request);

        if ($form->isValid()) {
            
            $gitAction =  $form->get('gitaction')->getData();
            
            $em = $this->getDoctrine()->getManager();
            
            //Set Project
            $projectEnvironment->setProject($project);
           
            if($gitAction === 'new'){
                //Create git repo
                $this->createEmptyGitRepository($projectEnvironment);
            }else if($gitAction === 'clone'){
                //Create Git Clone
                
                $this->cloneGitRepository($projectEnvironment);
            }
            
            $em->persist($projectEnvironment);
            $em->flush();

            return $this->redirect($this->generateUrl('project_edit', array('id' => $project->getId())));
        }

        return array(
            'project' => $project,
            'projectEnvironment' => $projectEnvironment,
            'form'   => $form->createView(),
        );
    }


    /**
     * Creates a form to create a Project entity.
     *
     * @param Project $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(ProjectEnvironment $entity,$project,$gitaction = '')
    {
        $form = $this->createForm(new ProjectEnvironmentType(true), $entity, array(
            'action' => $this->generateUrl('projectenvironment_create',array('project' => $project->getId())),
            'method' => 'POST',
        ));
        
        $form->add('gitaction', 'hidden', array(
            'mapped' => false,
            'empty_data' => false,
            'required' => 'required',
            'data'=>$gitaction
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new Project entity.
     *
     * @Route("/new/{project}", name="projectenvironment_new")
     * @ParamConverter("project", class="VersionContolGitControlBundle:Project")
     * @Method("GET")
     * @Template("VersionContolGitControlBundle:ProjectEnvironment:new.html.twig")
     */
    public function newAction($project)
    {
        $this->checkProjectAuthorization($project);
         
        $projectEnvironment = new ProjectEnvironment();
        $form   = $this->createCreateForm($projectEnvironment,$project,'new');
        
        

        return array(
            'project' => $project,
            'projectEnvironment' => $projectEnvironment,
            'form'   => $form->createView(),
        );
    }
    
    /**
     * Displays a form to create a new Project entity.
     *
     * @Route("/clone/{project}", name="projectenvironment_clone")
     * @ParamConverter("project", class="VersionContolGitControlBundle:Project")
     * @Method("GET")
     * @Template("VersionContolGitControlBundle:ProjectEnvironment:new.html.twig")
     */
    public function cloneAction($project)
    {
        $this->checkProjectAuthorization($project);
         
        $projectEnvironment = new ProjectEnvironment();
        $form   = $this->createCreateForm($projectEnvironment,$project,'clone');

        return array(
            'project' => $project,
            'projectEnvironment' => $projectEnvironment,
            'form'   => $form->createView(),
        );
    }
    
    /**
     * Displays a form to create a new Project entity.
     *
     * @Route("/existing/{project}", name="projectenvironment_existing")
     * @ParamConverter("project", class="VersionContolGitControlBundle:Project")
     * @Method("GET")
     * @Template("VersionContolGitControlBundle:ProjectEnvironment:new.html.twig")
     */
    public function existingAction($project)
    {
        $this->checkProjectAuthorization($project);
         
        $projectEnvironment = new ProjectEnvironment();
        $form   = $this->createCreateForm($projectEnvironment,$project);

        return array(
            'project' => $project,
            'projectEnvironment' => $projectEnvironment,
            'form'   => $form->createView(),
        );
    }


    
    /**
     * Displays a form to edit an existing Project entity.
     *
     * @Route("/{id}/edit", name="projectenvironment_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $projectEnvironment = $em->getRepository('VersionContolGitControlBundle:ProjectEnvironment')->find($id);

        if (!$projectEnvironment) {
            throw $this->createNotFoundException('Unable to find Project Environment entity.');
        }
        
        $this->checkProjectAuthorization($projectEnvironment->getProject(),'MASTER');

        $editForm = $this->createEditForm($projectEnvironment);
        $deleteForm = $this->createDeleteForm($id);
        
        
        return array(
            'project'     => $projectEnvironment->getProject(),
            'projectEnvironment'     => $projectEnvironment,
            'form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
    * Creates a form to edit a Project entity.
    *
    * @param Project $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(ProjectEnvironment $entity)
    {
        $form = $this->createForm(new ProjectEnvironmentType(), $entity, array(
            'action' => $this->generateUrl('projectenvironment_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing Project entity.
     *
     * @Route("/{id}", name="projectenvironment_update")
     * @Method("PUT")
     * @Template("VersionContolGitControlBundle:ProjectEnvironment:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $projectEnvironment = $em->getRepository('VersionContolGitControlBundle:ProjectEnvironment')->find($id);

        if (!$projectEnvironment) {
            throw $this->createNotFoundException('Unable to find Project Environment entity.');
        }
        
        $this->checkProjectAuthorization($projectEnvironment->getProject(),'MASTER');

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($projectEnvironment);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();
            
            $this->get('session')->getFlashBag()->add('success',"Project Environment record updated");

            return $this->redirect($this->generateUrl('projectenvironment_edit', array('id' => $id)));
        }

        return array(
            'project'      => $projectEnvironment->getProject(),
            'projectEnvironment'      => $projectEnvironment,
            'form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Project entity.
     *
     * @Route("/{id}", name="projectenvironment_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $projectEnvironment = $em->getRepository('VersionContolGitControlBundle:ProjectEnvironment')->find($id);

        if (!$projectEnvironment) {
            throw $this->createNotFoundException('Unable to find Project Environment entity.');
        }
        
        $this->checkProjectAuthorization($projectEnvironment->getProject(),'MASTER');
        $projectId = $projectEnvironment->getProject()->getId();    
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);
        
        
        if ($form->isValid()) {
            $em->remove($projectEnvironment);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('project_edit',array('id'=>$projectId)));
    }

    /**
     * Creates a form to delete a Project entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('projectenvironment_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
    
    protected function createEmptyGitRepository($projectEnvironment){
        $gitCommands = $this->get('version_control.git_init')->overRideProjectEnvironment($projectEnvironment);
        
        $response = $gitCommands->initRepository();
            
        $this->get('session')->getFlashBag()->add('notice', $response);
    }
    
    protected function cloneGitRepository($projectEnvironment){
        
        $gitCommands = $this->get('version_control.git_init')->overRideProjectEnvironment($projectEnvironment);
        try{
            $response = $gitCommands->cloneRepository($projectEnvironment->getGitCloneLocation());
            $this->get('session')->getFlashBag()->add('notice', $response);
        }catch(\Exception $e){
            $this->get('session')->getFlashBag()->add('error', $e->getMessage());
        }
            
        
        
    }
    


}
