<?php

namespace VersionContol\GitControlBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use VersionContol\GitControlBundle\Entity\Issue;
use VersionContol\GitControlBundle\Form\IssueType;

/**
 * Issue controller.
 *
 * @Route("/issue")
 */
class IssueController extends Controller
{

    /**
     * Lists all Issue entities.
     *
     * @Route("/{id}", name="issues")
     * @Method("GET")
     * @Template()
     */
    public function indexAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        
         $project= $em->getRepository('VersionContolGitControlBundle:Project')->find($id);

        if (!$project) {
            throw $this->createNotFoundException('Unable to find Project entity.');
        }
        
        //$this->checkProjectAuthorization($project,'EDIT');
        

        $entities = $em->getRepository('VersionContolGitControlBundle:Issue')->findAll();

        return array(
            'entities' => $entities,
            'project' => $project
        );
    }
    /**
     * Creates a new Issue entity.
     *
     * @Route("/", name="issue_create")
     * @Method("POST")
     * @Template("VersionContolGitControlBundle:Issue:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new Issue();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('issue_show', array('id' => $entity->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a form to create a Issue entity.
     *
     * @param Issue $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Issue $entity)
    {
        $form = $this->createForm(new IssueType(), $entity, array(
            'action' => $this->generateUrl('issue_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new Issue entity.
     *
     * @Route("/new", name="issue_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Issue();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Issue entity.
     *
     * @Route("/{id}", name="issue_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('VersionContolGitControlBundle:Issue')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Issue entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Issue entity.
     *
     * @Route("/{id}/edit", name="issue_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('VersionContolGitControlBundle:Issue')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Issue entity.');
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
    * Creates a form to edit a Issue entity.
    *
    * @param Issue $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Issue $entity)
    {
        $form = $this->createForm(new IssueType(), $entity, array(
            'action' => $this->generateUrl('issue_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing Issue entity.
     *
     * @Route("/{id}", name="issue_update")
     * @Method("PUT")
     * @Template("VersionContolGitControlBundle:Issue:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('VersionContolGitControlBundle:Issue')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Issue entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('issue_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Issue entity.
     *
     * @Route("/{id}", name="issue_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('VersionContolGitControlBundle:Issue')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Issue entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('issue'));
    }

    /**
     * Creates a form to delete a Issue entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('issue_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
}
