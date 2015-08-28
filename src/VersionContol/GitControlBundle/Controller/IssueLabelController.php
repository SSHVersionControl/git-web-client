<?php

namespace VersionContol\GitControlBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use VersionContol\GitControlBundle\Entity\IssueLabel;
use VersionContol\GitControlBundle\Form\IssueLabelType;

/**
 * IssueLabel controller.
 *
 * @Route("/issuelabel")
 */
class IssueLabelController extends Controller
{

    /**
     * Lists all IssueLabel entities.
     *
     * @Route("/", name="issuelabel")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('VersionContolGitControlBundle:IssueLabel')->findAll();

        return array(
            'entities' => $entities,
        );
    }
    /**
     * Creates a new IssueLabel entity.
     *
     * @Route("/", name="issuelabel_create")
     * @Method("POST")
     * @Template("VersionContolGitControlBundle:IssueLabel:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new IssueLabel();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('issuelabel_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a form to create a IssueLabel entity.
     *
     * @param IssueLabel $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(IssueLabel $entity)
    {
        $form = $this->createForm(new IssueLabelType(), $entity, array(
            'action' => $this->generateUrl('issuelabel_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new IssueLabel entity.
     *
     * @Route("/new", name="issuelabel_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new IssueLabel();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a IssueLabel entity.
     *
     * @Route("/{id}", name="issuelabel_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('VersionContolGitControlBundle:IssueLabel')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find IssueLabel entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing IssueLabel entity.
     *
     * @Route("/{id}/edit", name="issuelabel_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('VersionContolGitControlBundle:IssueLabel')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find IssueLabel entity.');
        }

        $editForm = $this->createEditForm($entity);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
    * Creates a form to edit a IssueLabel entity.
    *
    * @param IssueLabel $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(IssueLabel $entity)
    {
        $form = $this->createForm(new IssueLabelType(), $entity, array(
            'action' => $this->generateUrl('issuelabel_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing IssueLabel entity.
     *
     * @Route("/{id}", name="issuelabel_update")
     * @Method("PUT")
     * @Template("VersionContolGitControlBundle:IssueLabel:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('VersionContolGitControlBundle:IssueLabel')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find IssueLabel entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('issuelabel_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a IssueLabel entity.
     *
     * @Route("/{id}", name="issuelabel_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('VersionContolGitControlBundle:IssueLabel')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find IssueLabel entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('issuelabel'));
    }

    /**
     * Creates a form to delete a IssueLabel entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('issuelabel_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
