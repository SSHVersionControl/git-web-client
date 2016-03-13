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

use VersionContol\GitControlBundle\Annotation\ProjectAccess;

/**
 * Project controller.
 *
 * @Route("/project/{id}/issue-integrator")
 */
class ProjectIssueIntegratorController extends BaseProjectController{
    //put your code here

    protected $projectGrantType = 'OWNER';
    
    /**
     * Deletes a ProjectIssueIntegrator entity.
     *
     * @Route("/delete/{integratorId}", name="project_issue_integrator_delete")
     * @Method("DELETE")
     * @ProjectAccess(grantType="OWNER")
     */
    public function deleteAction(Request $request, $id, $integratorId)
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
     * @Route("/", name="project_issue_integrator")
     * @Template()
     * @ProjectAccess(grantType="OWNER")
     */
    public function indexAction($id){
        
        $em = $this->getDoctrine()->getManager();

        $issues = array();
        $issueIntegrator= $em->getRepository('VersionContolGitControlBundle:ProjectIssueIntegrator')->findOneByProject($this->project);

        return array_merge($this->viewVariables, array(
            'issues' => $issues,
            'issueIntegrator' => $issueIntegrator
        ));
        
    }
    
}
