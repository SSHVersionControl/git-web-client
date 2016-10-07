<?php
/*
 * This file is part of the GitControlBundle package.
 *
 * (c) Paul Schweppe <paulschweppe@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace VersionControl\GitControlBundle\Controller\Admin;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use VersionControl\GitControlBundle\Entity\IssueLabel;
use VersionControl\GitControlBundle\Form\IssueLabelType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

use Symfony\Component\Form\Extension\Core\Type\SubmitType;

/**
 * IssueLabel controller.
 *
 * @Route("/admin/issuelabel")
 * @Security("has_role('ROLE_ADMIN')")
 */
class IssueLabelAdminController extends Controller
{

    /**
     * Lists all IssueLabel entities.
     *
     * @Route("s/", name="admin_issuelabels")
     * @Method("GET")
     * @Template("VersionControlGitControlBundle:Admin/IssueLabel:index.html.twig")
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $entities = $em->getRepository('VersionControlGitControlBundle:IssueLabel')->findBy(array('allProjects'=>1));
        
        return array(
            'entities' => $entities
        );
    }
    /**
     * Creates a new IssueLabel entity.
     *
     * @Route("/", name="admin_issuelabel_create")
     * @Method("POST")
     * @Template("VersionControlGitControlBundle:Admin/IssueLabel:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $issue = new IssueLabel();
        $form = $this->createCreateForm($issue);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $issue->setAllProjects(true);
            $em = $this->getDoctrine()->getManager();
            $em->persist($issue);
            $em->flush();

            return $this->redirect($this->generateUrl('admin_issuelabels'));
        }

        return array(
            'entity' => $issue,
            'form'   => $form->createView(),
        );
    }

    /**
     * Creates a form to create a IssueLabel entity.
     *
     * @param IssueLabel $issue The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(IssueLabel $issue)
    {
        $form = $this->createForm(IssueLabelType::class, $issue, array(
            'action' => $this->generateUrl('admin_issuelabel_create'),
            'method' => 'POST',
        ));

        $form->add('submit', SubmitType::class, array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new IssueLabel entity.
     *
     * @Route("/new/", name="admin_issuelabel_new")
     * @Method("GET")
     * @Template("VersionControlGitControlBundle:Admin/IssueLabel:new.html.twig")
     */
    public function newAction()
    {
        $issueLabel = new IssueLabel();

        $form   = $this->createCreateForm($issueLabel);

        return array(
            'entity' => $issueLabel,
            'form'   => $form->createView(),

        );
    }

    

    /**
     * Displays a form to edit an existing IssueLabel entity.
     *
     * @Route("/{id}/edit/", name="admin_issuelabel_edit")
     * @Method("GET")
     * @Template("VersionControlGitControlBundle:Admin/IssueLabel:edit.html.twig")
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $issue = $em->getRepository('VersionControlGitControlBundle:IssueLabel')->find($id);

        if (!$issue) {
            throw $this->createNotFoundException('Unable to find IssueLabel entity.');
        }

        $editForm = $this->createEditForm($issue);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'entity'      => $issue,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
    * Creates a form to edit a IssueLabel entity.
    *
    * @param IssueLabel $issue The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(IssueLabel $issue)
    {
        $form = $this->createForm(IssueLabelType::class, $issue, array(
            'action' => $this->generateUrl('admin_issuelabel_update', array('id' => $issue->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', SubmitType::class, array('label' => 'Update'));

        return $form;
    }
    
    /**
     * Edits an existing IssueLabel entity.
     *
     * @Route("/{id}", name="admin_issuelabel_update")
     * @Method("PUT")
     * @Template("VersionControlGitControlBundle:Admin/IssueLabel:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $issue = $em->getRepository('VersionControlGitControlBundle:IssueLabel')->find($id);

        if (!$issue) {
            throw $this->createNotFoundException('Unable to find IssueLabel entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($issue);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('admin_issuelabel_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $issue,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a IssueLabel entity.
     *
     * @Route("/{id}", name="admin_issuelabel_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $issue = $em->getRepository('VersionControlGitControlBundle:IssueLabel')->find($id);

            if (!$issue) {
                throw $this->createNotFoundException('Unable to find IssueLabel entity.');
            }

            $em->remove($issue);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('admin_issuelabel'));
    }

    /**
     * Creates a form to delete a IssueLabel entity by id.
     *
     * @param mixed $id The entity id
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('admin_issuelabel_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', SubmitType::class, array('label' => 'Delete'))
            ->getForm()
        ;
    }
    
}
