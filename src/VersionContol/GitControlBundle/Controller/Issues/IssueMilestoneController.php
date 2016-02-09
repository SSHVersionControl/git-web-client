<?php

namespace VersionContol\GitControlBundle\Controller\Issues;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use VersionContol\GitControlBundle\Entity\IssueMilestone;
use VersionContol\GitControlBundle\Form\IssueMilestoneType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
 * IssueMilestone controller.
 *
 * @Route("/issuemilestone")
 */
class IssueMilestoneController extends Controller
{

    /**
     * Lists all IssueMilestone entities.
     *
     * @Route("s/{project}", name="issuemilestones")
     * @ParamConverter("project", class="VersionContolGitControlBundle:Project")
     * @Method("GET")
     * @Template()
     */
    public function indexAction($project, Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        //$entities = $em->getRepository('VersionContolGitControlBundle:IssueMilestone')->findAll();
        
        //$entities = $em->getRepository('VersionContolGitControlBundle:IssueMilestone')->findByProject($project);
        //$openIssuesCount = $em->getRepository('VersionContolGitControlBundle:Issue')->countIssuesForProjectWithStatus($project,'open');
        //$closedIssuesCount = $em->getRepository('VersionContolGitControlBundle:Issue')->countIssuesForProjectWithStatus($project,'closed');
        
        $keyword = $request->query->get('keyword', false);
        $filter = $request->query->get('filter', 'open');
        
        $query = $em->getRepository('VersionContolGitControlBundle:IssueMilestone')->findByProjectAndStatus($project,$filter,$keyword,true)->getQuery();
        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1)/*page number*/,
            15/*limit per page*/
        );

    
        $openIssuesCount = $em->getRepository('VersionContolGitControlBundle:IssueMilestone')->countForProjectWithStatus($project,'open',$keyword);
        $closedIssuesCount = $em->getRepository('VersionContolGitControlBundle:IssueMilestone')->countForProjectWithStatus($project,'closed',$keyword);
        

        return array(
            'project' => $project,
            'openCount' => $openIssuesCount,
            'closedCount' => $closedIssuesCount,
            'pagination' => $pagination
        );

            
    }
    /**
     * Creates a new IssueMilestone entity.
     *
     * @Route("/", name="issuemilestone_create")
     * @Method("POST")
     * @Template("VersionContolGitControlBundle:IssueMilestone:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $issueMilestone = new IssueMilestone();
        $form = $this->createCreateForm($issueMilestone);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $user = $this->get('security.token_storage')->getToken()->getUser();
            $issueMilestone->setVerUser($user);
            $em->persist($issueMilestone);
            $em->flush();
            
            $this->get('session')->getFlashBag()->add('notice', 'New Milestone:'.$issueMilestone->getTitle());

            return $this->redirect($this->generateUrl('issuemilestone_show', array('id' => $issueMilestone->getId())));
        }

        return array(
            'entity' => $issueMilestone,
            'project' => $issueMilestone->getProject(),
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a form to create a IssueMilestone entity.
     *
     * @param IssueMilestone $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(IssueMilestone $entity)
    {
        $form = $this->createForm(new IssueMilestoneType(), $entity, array(
            'action' => $this->generateUrl('issuemilestone_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new IssueMilestone entity.
     *
     * @Route("/new/{project}", name="issuemilestone_new")
     * @ParamConverter("project", class="VersionContolGitControlBundle:Project")
     * @Method("GET")
     * @Template()
     */
    public function newAction($project)
    {
        $entity = new IssueMilestone();
        $entity->setProject($project);
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'project' => $project,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a IssueMilestone entity.
     *
     * @Route("/{id}", name="issuemilestone_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('VersionContolGitControlBundle:IssueMilestone')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find IssueMilestone entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        
        $openCount = $em->getRepository('VersionContolGitControlBundle:Issue')->countIssuesForProjectWithStatus($entity->getProject(),'open',false,$entity);

        $closedCount = $em->getRepository('VersionContolGitControlBundle:Issue')->countIssuesForProjectWithStatus($entity->getProject(),'closed',false,$entity);
        

        return array(
            'entity'      => $entity,
            'project'      => $entity->getProject(),
            'delete_form' => $deleteForm->createView(),
            'openCount' => $openCount,
            'closedCount' => $closedCount,
        );
    }

    /**
     * Displays a form to edit an existing IssueMilestone entity.
     *
     * @Route("/{id}/edit", name="issuemilestone_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('VersionContolGitControlBundle:IssueMilestone')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find IssueMilestone entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'milestone'      => $entity,
            'project'   => $entity->getProject(),
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
    * Creates a form to edit a IssueMilestone entity.
    *
    * @param IssueMilestone $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(IssueMilestone $entity)
    {
        $form = $this->createForm(new IssueMilestoneType(), $entity, array(
            'action' => $this->generateUrl('issuemilestone_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing IssueMilestone entity.
     *
     * @Route("/{id}", name="issuemilestone_update")
     * @Method("PUT")
     * @Template("VersionContolGitControlBundle:IssueMilestone:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('VersionContolGitControlBundle:IssueMilestone')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find IssueMilestone entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();
            $this->get('session')->getFlashBag()->add('notice', 'Updated Milestone:'.$entity->getTitle());
            return $this->redirect($this->generateUrl('issuemilestone_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a IssueMilestone entity.
     *
     * @Route("/{id}", name="issuemilestone_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('VersionContolGitControlBundle:IssueMilestone')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find IssueMilestone entity.');
            }

            $this->get('session')->getFlashBag()->add('notice', 'Deleted Milestone:'.$entity->getTitle());
            
            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('issuemilestone'));
    }

    /**
     * Creates a form to delete a IssueMilestone entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('issuemilestone_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
    
    /**
     * Displays a form to edit an existing Issue entity.
     *
     * @Route("/{id}/close", name="issuemilestone_close")
     * @Method("GET")
     */
    public function closeAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $issueMilestone = $em->getRepository('VersionContolGitControlBundle:IssueMilestone')->find($id);

        if (!$issueMilestone) {
            throw $this->createNotFoundException('Unable to find Issue Milestone entity.');
        }
        
        $issueMilestone->setClosed();
        $em->flush();
        
        $this->get('session')->getFlashBag()->add('notice'
                ,"Milestone #".$issueMilestone->getId()." has been closed");
        
        return $this->redirect($this->generateUrl('issuemilestones', array('project' => $issueMilestone->getProject()->getId())));
    }
    
     /**
     * Displays a form to edit an existing Issue entity.
     *
     * @Route("/{id}/open", name="issuemilestone_open")
     * @Method("GET")
     */
    public function openAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $issueMilestone = $em->getRepository('VersionContolGitControlBundle:IssueMilestone')->find($id);

        if (!$issueMilestone) {
            throw $this->createNotFoundException('Unable to find Issue Milestone entity.');
        }
        
        $issueMilestone->setOpen();
        $em->flush();
        
        $this->get('session')->getFlashBag()->add('notice'
                ,"Milestone #".$issueMilestone->getId()." has been opened");
        
        return $this->redirect($this->generateUrl('issuemilestone_show', array('id' => $issueMilestone->getId())));

    }
    
    /**
     * Lists all Issue entities.
     *
     * @Template()
     */
    public function milestonesIssuesAction(Request $request,$issueMilestone,$filter = 'open',$pageParameterName='page',$keywordParamaterName='keyword')
    {
        $parentRequest =  $request->createFromGlobals();
        
        $em = $this->getDoctrine()->getManager();
     
        $keyword = $parentRequest->query->get($keywordParamaterName, false);

        $query = $em->getRepository('VersionContolGitControlBundle:Issue')->findByProjectAndStatus($issueMilestone->getProject(),$filter,$keyword,$issueMilestone,true)->getQuery();
        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $parentRequest->query->getInt($pageParameterName, 1)/*page number*/,
            10,/*limit per page*/
            array('pageParameterName' => $pageParameterName)
        );

        return array(
            'issueMilestone' => $issueMilestone,
            'project' => $issueMilestone->getProject(),
            'pagination' => $pagination,
            'status' => $filter,
            'keywordParamaterName' => $keywordParamaterName,
            'keyword' => $keyword
             
            
        );
    }
}
