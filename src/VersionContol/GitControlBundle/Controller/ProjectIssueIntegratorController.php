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

use VersionControl\GithubIssueBundle\Entity\ProjectIssueIntegratorGithub;
use VersionControl\GithubIssueBundle\Form\ProjectIssueIntegratorGithubType;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Project controller.
 *
 * @Route("/project/issue-integrator")
 */
class ProjectIssueIntegratorController extends BaseProjectController{
    //put your code here

    /**
     * Deletes a ProjectIssueIntegrator entity.
     *
     * @Route("/{id}/delete/{integratorId}", name="project_issue_integrator_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id, $integratorId)
    {
        $this->initAction($id);
        
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

        return $this->redirect($this->generateUrl('project_issue_integrator',array('id' => $id)));
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
