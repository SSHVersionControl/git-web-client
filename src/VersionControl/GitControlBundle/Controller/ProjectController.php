<?php

namespace VersionControl\GitControlBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use VersionControl\GitControlBundle\Entity\Project;
use VersionControl\GitControlBundle\Form\ProjectType;
use VersionControl\GitControlBundle\Utility\GitCommands;
use Symfony\Component\Validator\Constraints\NotBlank;
use VersionControl\GitControlBundle\Entity\UserProjects;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Project controller.
 *
 * @Route("/project")
 */
class ProjectController extends Controller
{

    /**
     * Lists all Project entities.
     *
     * @Route("/", name="project")
     * @Method("GET")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        //$entities = $em->getRepository('VersionControlGitControlBundle:Project')->findAll();
        $keyword = $request->query->get('keyword', false);
        
        $query = $em->getRepository('VersionControlGitControlBundle:Project')->findByKeyword($keyword,true)->getQuery();
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
     * @Route("/", name="project_create")
     * @Method("POST")
     * @Template("VersionControlGitControlBundle:Project:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $project = new Project();
        $form = $this->createCreateForm($project);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            
            //Get User
            $user = $this->get('security.token_storage')->getToken()->getUser();
            
            //Set Creator
            $project->setCreator($user);
            
            //Set Access and Roles
            $userProjectAccess = new UserProjects();
            $userProjectAccess->setUser($user);
            $userProjectAccess->setRoles('Owner');
            $project->addUserProjects($userProjectAccess);
            
            $em->persist($project);
            $em->flush();

            return $this->redirect($this->generateUrl('project_edit', array('id' => $project->getId())));
        }

        return array(
            'entity' => $project,
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
    private function createCreateForm(Project $entity)
    {
        $form = $this->createForm(new ProjectType(), $entity, array(
            'action' => $this->generateUrl('project_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new Project entity.
     *
     * @Route("/new", name="project_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Project();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }


    
    /**
     * Displays a form to edit an existing Project entity.
     *
     * @Route("/{id}/edit", name="project_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $project = $em->getRepository('VersionControlGitControlBundle:Project')->find($id);

        if (!$project) {
            throw $this->createNotFoundException('Unable to find Project entity.');
        }
        
        $this->checkProjectAuthorization($project,'MASTER');

        $editForm = $this->createEditForm($project);
        $deleteForm = $this->createDeleteForm($id);
        
        return array(
            'project'      => $project,
            'edit_form'   => $editForm->createView(),
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
    private function createEditForm(Project $entity)
    {
        $form = $this->createForm(new ProjectType(), $entity, array(
            'action' => $this->generateUrl('project_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing Project entity.
     *
     * @Route("/{id}", name="project_update")
     * @Method("PUT")
     * @Template("VersionControlGitControlBundle:Project:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $project = $em->getRepository('VersionControlGitControlBundle:Project')->find($id);

        if (!$project) {
            throw $this->createNotFoundException('Unable to find Project entity.');
        }
        
        $this->checkProjectAuthorization($project,'MASTER');

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($project);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();
            
            $this->get('session')->getFlashBag()->add('notice',"Project record updated");

            return $this->redirect($this->generateUrl('project_edit', array('id' => $id)));
        }

        return array(
            'project'      => $project,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Project entity.
     *
     * @Route("/{id}", name="project_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $project = $em->getRepository('VersionControlGitControlBundle:Project')->find($id);

        if (!$project) {
            throw $this->createNotFoundException('Unable to find Project entity.');
        }
        
        $this->checkProjectAuthorization($project,'MASTER');
            
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);
        
        
        if ($form->isValid()) {
            $em->remove($project);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('project'));
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
            ->setAction($this->generateUrl('project_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
    
    
    /**
     * 
     * @param VersionControl\GitControlBundle\Entity\Project $project
     * @throws AccessDeniedException
     */
    protected function checkProjectAuthorization(\VersionControl\GitControlBundle\Entity\Project $project,$grantType='MASTER'){
        $authorizationChecker = $this->get('security.authorization_checker');

        // check for edit access
        if (false === $authorizationChecker->isGranted($grantType, $project)) {
            throw new AccessDeniedException();
        }
    }

}
