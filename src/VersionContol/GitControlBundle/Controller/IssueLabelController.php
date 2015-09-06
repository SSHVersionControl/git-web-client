<?php

namespace VersionContol\GitControlBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
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
     * @Route("s/{project}", name="issuelabels")
     * @ParamConverter("project", class="VersionContolGitControlBundle:Project")
     * @Method("GET")
     * @Template()
     */
    public function indexAction($project)
    {
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('VersionContolGitControlBundle:IssueLabel')->findAll();
        
     
        return array(
            'entities' => $entities,
            'project' => $project,
        );
    }
    /**
     * Creates a new IssueLabel entity.
     *
     * @Route("/{project}", name="issuelabel_create")
     * @ParamConverter("project", class="VersionContolGitControlBundle:Project")
     * @Method("POST")
     * @Template("VersionContolGitControlBundle:IssueLabel:new.html.twig")
     */
    public function createAction(Request $request,$project)
    {
        $issue = new IssueLabel();
        $form = $this->createCreateForm($issue,$project);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($issue);
            $em->flush();

            return $this->redirect($this->generateUrl('issuelabels', array('project' => $project->getId())));
        }

        return array(
            'entity' => $issue,
            'form'   => $form->createView(),
            'project' => $project,
        );
    }

    /**
     * Creates a form to create a IssueLabel entity.
     *
     * @param IssueLabel $issue The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(IssueLabel $issue,$project)
    {
        $form = $this->createForm(new IssueLabelType(), $issue, array(
            'action' => $this->generateUrl('issuelabel_create', array('project' => $project->getId())),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new IssueLabel entity.
     *
     * @Route("/new/{project}", name="issuelabel_new")
     * @ParamConverter("project", class="VersionContolGitControlBundle:Project")
     * @Method("GET")
     * @Template()
     */
    public function newAction($project)
    {
        $issue = new IssueLabel();
        $form   = $this->createCreateForm($issue,$project);

        return array(
            'entity' => $issue,
            'form'   => $form->createView(),
            'project' => $project,
        );
    }

    

    /**
     * Displays a form to edit an existing IssueLabel entity.
     *
     * @Route("/{id}/edit/{project}", name="issuelabel_edit")
     * @ParamConverter("project", class="VersionContolGitControlBundle:Project")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id,$project)
    {
        $em = $this->getDoctrine()->getManager();

        $issue = $em->getRepository('VersionContolGitControlBundle:IssueLabel')->find($id);

        if (!$issue) {
            throw $this->createNotFoundException('Unable to find IssueLabel entity.');
        }

        $editForm = $this->createEditForm($issue,$project);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $issue,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'project' => $project
        );
    }

    /**
    * Creates a form to edit a IssueLabel entity.
    *
    * @param IssueLabel $issue The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(IssueLabel $issue, $project)
    {
        $form = $this->createForm(new IssueLabelType(), $issue, array(
            'action' => $this->generateUrl('issuelabel_update', array('id' => $issue->getId(),'project' => $project->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing IssueLabel entity.
     *
     * @Route("/{id}/{project}", name="issuelabel_update")
     * @ParamConverter("project", class="VersionContolGitControlBundle:Project")
     * @Method("PUT")
     * @Template("VersionContolGitControlBundle:IssueLabel:edit.html.twig")
     */
    public function updateAction(Request $request, $id, $project)
    {
        $em = $this->getDoctrine()->getManager();

        $issue = $em->getRepository('VersionContolGitControlBundle:IssueLabel')->find($id);

        if (!$issue) {
            throw $this->createNotFoundException('Unable to find IssueLabel entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($issue,$project);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('issuelabel_edit', array('id' => $id, 'project' => $project->getId())));
        }

        return array(
            'entity'      => $issue,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'project' => $project
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
            $issue = $em->getRepository('VersionContolGitControlBundle:IssueLabel')->find($id);

            if (!$issue) {
                throw $this->createNotFoundException('Unable to find IssueLabel entity.');
            }

            $em->remove($issue);
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
