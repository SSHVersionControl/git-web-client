<?php

namespace VersionContol\GitControlBundle\Controller;

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
    public function indexAction($project)
    {
        $em = $this->getDoctrine()->getManager();

        //$entities = $em->getRepository('VersionContolGitControlBundle:IssueMilestone')->findAll();
        
        $entities = $em->getRepository('VersionContolGitControlBundle:IssueMilestone')->findByProject($project);
        //$openIssuesCount = $em->getRepository('VersionContolGitControlBundle:Issue')->countIssuesForProjectWithStatus($project,'open');
        //$closedIssuesCount = $em->getRepository('VersionContolGitControlBundle:Issue')->countIssuesForProjectWithStatus($project,'closed');

        return array(
            'entities' => $entities,
            'project' => $project
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
            
            $this->get('session')->getFlashBag()->add('notice', 'New Milestone:'.$entity->getTitle());

            return $this->redirect($this->generateUrl('issuemilestone_show', array('id' => $issueMilestone->getId())));
        }

        return array(
            'entity' => $issueMilestone,
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

        return array(
            'entity'      => $entity,
            'project'      => $entity->getProject(),
            'delete_form' => $deleteForm->createView(),
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
}
