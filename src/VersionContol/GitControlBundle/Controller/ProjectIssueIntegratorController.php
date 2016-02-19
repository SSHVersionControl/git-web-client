<?php
namespace VersionContol\GitControlBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use VersionContol\GitControlBundle\Controller\Base\BaseProjectController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use VersionContol\GitControlBundle\Entity\Project;
use VersionContol\GitControlBundle\Entity\ProjectIssueIntegrator;
use VersionContol\GitControlBundle\Form\ProjectIssueIntegratorType;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Project controller.
 *
 * @Route("/project/issue-integrator")
 */
class ProjectIssueIntegratorController extends BaseProjectController{
    //put your code here
    
    
    
   /**
     * Creates a new ProjectIssueIntegrator entity.
     *
     * @Route("/{id}", name="project_issue_integrator_create")
     * @Method("POST")
     * @Template("VersionContolGitControlBundle:ProjectIssueIntegrator:new.html.twig")
     */
    public function createAction(Request $request,$id)
    {
        $this->initAction($id);
        $issueIntegrator = new ProjectIssueIntegrator();
        $form = $this->createCreateForm($issueIntegrator);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($issueIntegrator);
            $em->flush();
            
            $this->get('session')->getFlashBag()->add('notice', 'Issue Integrator Record has been created');

            return $this->redirect($this->generateUrl('project_issue_integrator', array('id' => $this->project->getId())));
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
        $form = $this->createForm(new ProjectIssueIntegratorType(), $issueIntegrator, array(
            'action' => $this->generateUrl('project_issue_integrator_create', array('id' => $this->project->getId())),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new ProjectIssueIntegrator entity.
     *
     * @Route("/new/{id}", name="project_issue_integrator_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction($id)
    {
        $this->initAction($id);
        
        $issueIntegrator = new ProjectIssueIntegrator();
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
     * @Route("/{integratorId}/edit/{id}", name="project_issue_integrator_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id,$integratorId)
    {
        $this->initAction($id);
        
        $em = $this->getDoctrine()->getManager();

        $issueIntegrator = $em->getRepository('VersionContolGitControlBundle:ProjectIssueIntegrator')->find($integratorId);

        if (!$issueIntegrator) {
            throw $this->createNotFoundException('Unable to find ProjectIssueIntegrator entity.');
        }

        $editForm = $this->createEditForm($issueIntegrator);
        $deleteForm = $this->createDeleteForm($id);

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
        $form = $this->createForm(new ProjectIssueIntegratorType(), $issueIntegrator, array(
            'action' => $this->generateUrl('project_issue_integrator_update', array('integratorId' => $issueIntegrator->getId(),'id' => $this->project->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing ProjectIssueIntegrator entity.
     *
     * @Route("/{integratorId}/{id}", name="project_issue_integrator_update")
     * @Method("PUT")
     * @Template("VersionContolGitControlBundle:ProjectIssueIntegrator:edit.html.twig")
     */
    public function updateAction(Request $request,$integratorId, $id)
    {
        $this->initAction($id);
        
        $em = $this->getDoctrine()->getManager();

        $issueIntegrator = $em->getRepository('VersionContolGitControlBundle:ProjectIssueIntegrator')->find($integratorId);

        if (!$issueIntegrator) {
            throw $this->createNotFoundException('Unable to find ProjectIssueIntegrator entity.');
        }
        
        $project = $issueIntegrator->getProject();
        $this->checkProjectAuthorization($project,'OWNER');

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($issueIntegrator);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();
            
            $this->get('session')->getFlashBag()->add('notice', 'Issue Integrator Record has been update');

            return $this->redirect($this->generateUrl('project_issue_integrator_edit', array('id' => $id,'integratorId' => $integratorId)));
        }

        return array_merge($this->viewVariables, array(
            'entity'      => $issueIntegrator,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a ProjectIssueIntegrator entity.
     *
     * @Route("/{integratorId}", name="project_issue_integrator_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $integratorId)
    {
        $form = $this->createDeleteForm($integratorId);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $issueIntegrator = $em->getRepository('VersionContolGitControlBundle:ProjectIssueIntegrator')->find($integratorId);

            if (!$issueIntegrator) {
                throw $this->createNotFoundException('Unable to find ProjectIssueIntegrator entity.');
            }
            $project = $issueIntegrator->getProject();
            $this->checkProjectAuthorization($project,'OWNER');
            
            $em->remove($issueIntegrator);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('project_issue_integrator'));
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
            ->setAction($this->generateUrl('project_issue_integrator_delete', array('integratorId' => $integratorId)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
    
    /**
     * @Route("/{id}", name="project_issue_integrator")
     * @Template()
     */
    public function indexAction($id){
        
        $this->initAction($id);
        
        $em = $this->getDoctrine()->getManager();

        $issues = array();
        $issueIntegrator= $em->getRepository('VersionContolGitControlBundle:ProjectIssueIntegrator')->findOneByProject($this->project);

        if($issueIntegrator){ 
           //$client = new \Github\Client();
           //$issues = $client->api('issue')->all($issueIntegrator->getOwnerName(), $issueIntegrator->getRepoName(), array('state' => 'open'));
           //$client = new \Github\Client();
            //$this->client = new \Gitlab\Client('http://git.fluid-rock.com/api/v3/'); // change here
            //print_r($issueIntegrator->getApiToken());
            //$this->client->authenticate($issueIntegrator->getApiToken(), \Gitlab\Client::AUTH_URL_TOKEN);
            
            //print_r($this->client->api('projects')->all(1,50));
        }
        
        
        return array_merge($this->viewVariables, array(
            'issues' => $issues,
            'issueIntegrator' => $issueIntegrator
        ));
        
    }
    
    /**
     * 
     * @param integer $id Project Id
     */
    protected function initAction($id){
 
        $em = $this->getDoctrine()->getManager();

        $this->project= $em->getRepository('VersionContolGitControlBundle:Project')->find($id);

        if (!$this->project) {
            throw $this->createNotFoundException('Unable to find Project entity.');
        }
        
        $this->checkProjectAuthorization($this->project,'OWNER');

        $this->viewVariables = array_merge($this->viewVariables, array(
            'project'      => $this->project,
            ));
    }
    
}
