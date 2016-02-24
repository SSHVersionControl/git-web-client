<?php
namespace VersionControl\GitlabIssueBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use VersionContol\GitControlBundle\Controller\Base\BaseProjectController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use VersionContol\GitControlBundle\Entity\ProjectIssueIntegrator;


use VersionControl\GitlabIssueBundle\Entity\ProjectIssueIntegratorGitlab;
use VersionControl\GitlabIssueBundle\Form\ProjectIssueIntegratorGitlabType;
use VersionControl\GitlabIssueBundle\Form\ProjectIssueIntegratorGitlabNewType;
use VersionContol\GitControlBundle\Annotation\ProjectAccess;

/**
 * Project controller.
 *
 * @Route("/project/{id}/issue-integrator/gitlab")
 */
class ProjectIssueIntegratorGitlabController extends BaseProjectController{
    
   /**
     * Creates a new ProjectIssueIntegrator entity.
     *
     * @Route("/", name="project_issue_integrator_gitlab_create")
     * @Method("POST")
     * @Template("VersionControlGitlabIssueBundle:ProjectIssueIntegrator:new.html.twig")
     * @ProjectAccess(grantType="OWNER")
     */
    public function createAction(Request $request,$id)
    {
        
        $issueIntegrator = new ProjectIssueIntegratorGitlab();
        $form = $this->createCreateForm($issueIntegrator);
        $form->handleRequest($request);

        if ($form->isValid()) {
            
            $issueIntegrator->setRepoType('Gitlab');
            $issueIntegrator->setProject($this->project);
            
            $em = $this->getDoctrine()->getManager();
            $em->persist($issueIntegrator);
            $em->flush();
            
            $this->get('session')->getFlashBag()->add('notice', 'Issue Integrator Record has been created');

            return $this->redirect($this->generateUrl('project_issue_integrator_gitlab_edit', array('id' => $this->project->getId(), 'integratorId'=> $issueIntegrator->getId())));
        }

        return array_merge($this->viewVariables, array(
            'entity' => $issueIntegrator,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a ProjectIssueIntegrator entity.
     *
     * @param ProjectIssueIntegrator $issueIntegrator The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(ProjectIssueIntegrator $issueIntegrator)
    {
        $form = $this->createForm(new ProjectIssueIntegratorGitlabNewType(), $issueIntegrator, array(
            'action' => $this->generateUrl('project_issue_integrator_gitlab_create', array('id' => $this->project->getId())),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new ProjectIssueIntegrator entity.
     *
     * @Route("/new/", name="project_issue_integrator_gitlab_new")
     * @Method("GET")
     * @Template()
     * @ProjectAccess(grantType="OWNER")
     */
    public function newAction($id)
    {
        
        
        $issueIntegrator = new ProjectIssueIntegratorGitlab();
        $issueIntegrator->setProject($this->project);
        $form   = $this->createCreateForm($issueIntegrator);

        return array_merge($this->viewVariables, array(
            'entity' => $issueIntegrator,
            'form'   => $form->createView(),
        ));
    }

    

    /**
     * Displays a form to edit an existing ProjectIssueIntegrator entity.
     *
     * @Route("/{integratorId}/edit/", name="project_issue_integrator_gitlab_edit")
     * @Method("GET")
     * @Template()
     * @ProjectAccess(grantType="OWNER")
     */
    public function editAction($id,$integratorId)
    {
        
        
        $em = $this->getDoctrine()->getManager();

        $issueIntegrator = $em->getRepository('VersionContolGitControlBundle:ProjectIssueIntegrator')->find($integratorId);

        if (!$issueIntegrator) {
            throw $this->createNotFoundException('Unable to find ProjectIssueIntegrator entity.');
        }

        $editForm = $this->createEditForm($issueIntegrator);
        $deleteForm = $this->createDeleteForm($integratorId);

        return array_merge($this->viewVariables, array(
            'entity'      => $issueIntegrator,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            
        ));
    }

    /**
    * Creates a form to edit a ProjectIssueIntegrator entity.
    *
    * @param ProjectIssueIntegrator $issueIntegrator The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(ProjectIssueIntegrator $issueIntegrator)
    {
        $form = $this->createForm(new ProjectIssueIntegratorGitlabType(), $issueIntegrator, array(
            'action' => $this->generateUrl('project_issue_integrator_gitlab_update', array('integratorId' => $issueIntegrator->getId(),'id' => $this->project->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing ProjectIssueIntegrator entity.
     *
     * @Route("/{integratorId}/", name="project_issue_integrator_gitlab_update")
     * @Method("PUT")
     * @Template("VersionControlGitlabIssueBundle:ProjectIssueIntegrator:edit.html.twig")
     * @ProjectAccess(grantType="OWNER")
     */
    public function updateAction(Request $request,$integratorId, $id)
    {
        
        
        $em = $this->getDoctrine()->getManager();

        $issueIntegrator = $em->getRepository('VersionContolGitControlBundle:ProjectIssueIntegrator')->find($integratorId);

        if (!$issueIntegrator) {
            throw $this->createNotFoundException('Unable to find ProjectIssueIntegrator entity.');
        }
        
        $project = $issueIntegrator->getProject();
        $this->checkProjectAuthorization($project,'OWNER');

        $deleteForm = $this->createDeleteForm($integratorId);
        $editForm = $this->createEditForm($issueIntegrator);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();
            
            $this->get('session')->getFlashBag()->add('notice', 'Issue Integrator Record has been update');

            return $this->redirect($this->generateUrl('project_issue_integrator_gitlab_edit', array('id' => $id,'integratorId' => $integratorId)));
        }

        return array_merge($this->viewVariables, array(
            'entity'      => $issueIntegrator,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    
    /**
     * Creates a form to delete a ProjectIssueIntegrator entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($integratorId)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('project_issue_integrator_delete', array('id' => $this->project->getId(), 'integratorId' => $integratorId)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
    
}
