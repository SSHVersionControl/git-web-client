<?php

namespace VersionControl\GitControlBundle\Controller\Issues;

use Symfony\Component\HttpFoundation\Request;
use VersionControl\GitControlBundle\Controller\Base\BaseProjectController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use VersionControl\GitControlBundle\Entity\Issues\IssueLabelInterface;
use VersionControl\GitControlBundle\Form\IssueLabelType;
use VersionControl\GitControlBundle\Annotation\ProjectAccess;

/**
 * IssueLabel controller.
 *
 * @Route("project/{id}/issuelabel")
 */
class IssueLabelController extends BaseProjectController
{

    /**
     * 
     * @var IssueLabelRepositoryInterface
     */
    protected $issueLabelRepository;
    
    /**
     *
     * @var \VersionControl\GitControlBundle\Repository\Issues\IssueRepositoryManager;
     */
    protected $issueManager;
    
    /**
     * Lists all IssueLabel entities.
     *
     * @Route("s/", name="issuelabels")
     * @Method("GET")
     * @Template()
     * @ProjectAccess(grantType="VIEW")
     */
    public function indexAction($id)
    {
        
        $issueLabels = $this->issueLabelRepository->listLabels();
        
        return array(
            'entities' => $issueLabels,
            'project' => $this->project,
        );
    }
    /**
     * Creates a new IssueLabel entity.
     *
     * @Route("/", name="issuelabel_create")
     * @Method("POST")
     * @Template("VersionControlGitControlBundle:IssueLabel:new.html.twig")
     * @ProjectAccess(grantType="EDIT")
     */
    public function createAction(Request $request,$id)
    {
        
        $issueLabel = $this->issueLabelRepository->newLabel();
        $form = $this->createCreateForm($issueLabel);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $issueLabel = $this->issueLabelRepository->createLabel($issueLabel);

            $this->get('session')->getFlashBag()->add('notice', "New Issue label Created ".$issueLabel->getId().".");
            
            $this->get('session')->getFlashBag()->add('info', "It may take a minute or two before you can see the new label in the list below");
            
            return $this->redirect($this->generateUrl('issuelabels', array('id' => $this->project->getId())));
        }

        return array(
            'entity' => $issueLabel,
            'form'   => $form->createView(),
            'project' => $this->project,
        );
    }

    /**
     * Creates a form to create a IssueLabel entity.
     *
     * @param IssueLabel $issueLabel The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(IssueLabelInterface $issueLabel)
    {
        $issueLabelFormType = $this->issueManager->getIssueLabelFormType();
        $form = $this->createForm($issueLabelFormType, $issueLabel, array(
            'action' => $this->generateUrl('issuelabel_create', array('id' => $this->project->getId())),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new IssueLabel entity.
     *
     * @Route("/new/", name="issuelabel_new")
     * @Method("GET")
     * @Template()
     * @ProjectAccess(grantType="EDIT")
     */
    public function newAction($id)
    {
         
        $issueLabel = $this->issueLabelRepository->newLabel();
        $form   = $this->createCreateForm($issueLabel);

        return array(
            'entity' => $issueLabel,
            'form'   => $form->createView(),
            'project' => $this->project,
        );
    }

    

    /**
     * Displays a form to edit an existing IssueLabel entity.
     *
     * @Route("/edit/{labelId}", name="issuelabel_edit")
     * @Method("GET")
     * @Template()
     * @ProjectAccess(grantType="EDIT")
     */
    public function editAction($id,$labelId)
    {
       
        $issueLabel = $this->issueLabelRepository->findLabelById($labelId);

        if (!$issueLabel) {
            throw $this->createNotFoundException('Unable to find IssueLabel entity.');
        }

        $editForm = $this->createEditForm($issueLabel);

        return array(
            'entity'      => $issueLabel,
            'edit_form'   => $editForm->createView(),
            'project' => $this->project
        );
    }

    /**
    * Creates a form to edit a IssueLabel entity.
    *
    * @param IssueLabel $issue The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(IssueLabelInterface $issue)
    {
        $issueLabelFormType = $this->issueManager->getIssueLabelFormType();
        $form = $this->createForm($issueLabelFormType, $issue, array(
            'action' => $this->generateUrl('issuelabel_update', array('labelId' => $issue->getId(),'id' => $this->project->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing IssueLabel entity.
     *
     * @Route("/{labelId}", name="issuelabel_update")
     * @Method("PUT")
     * @Template("VersionControlGitControlBundle:IssueLabel:edit.html.twig")
     * @ProjectAccess(grantType="EDIT")
     */
    public function updateAction(Request $request, $id, $labelId)
    {
       
        $issueLabel = $this->issueLabelRepository->findLabelById($labelId);

        if (!$issueLabel) {
            throw $this->createNotFoundException('Unable to find IssueLabel entity.');
        }

        $editForm = $this->createEditForm($issueLabel);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {

            $this->issueLabelRepository->updateLabel($issueLabel);
            
            $this->get('session')->getFlashBag()->add('notice', "Issue label Update '".$issueLabel->getId()."'.");
            //$this->get('session')->getFlashBag()->add('info', "It may take a minute or two before you can see the updated label in the list below");
            
            return $this->redirect($this->generateUrl('issuelabels', array('id' => $this->project->getId())));
        }

        return array(
            'entity'      => $issueLabel,
            'edit_form'   => $editForm->createView(),
            'project' => $this->project
        );
    }
    /**
     * Deletes a IssueLabel entity.
     *
     * @Route("/delete/{labelId}", name="issuelabel_delete")
     * @ProjectAccess(grantType="OWNER")
     */
    public function deleteAction($id, $labelId)
    {

        if ($labelId){
            $this->issueLabelRepository->deleteLabel($labelId); 
        }
        
        $this->get('session')->getFlashBag()->add('notice', "Issue label '".$labelId."' has been deleted");

        return $this->redirect($this->generateUrl('issuelabels',array('id' => $this->project->getId())));
    }

    
    /**
     * 
     * @param integer $id
     */
    public function initAction($id,$grantType='VIEW'){
        parent::initAction($id,$grantType);
        
        $em = $this->getDoctrine()->getManager();

        $issueIntegrator= $em->getRepository('VersionControlGitControlBundle:ProjectIssueIntegrator')->findOneByProject($this->project);
        
        $this->issueManager = $this->get('version_control.issue_repository_manager');
        if($issueIntegrator){
            $this->issueManager->setIssueIntegrator($issueIntegrator);
        }else{
            $this->issueManager->setProject($this->project);
        }
        $this->issueLabelRepository = $this->issueManager->getIssueLabelRepository();
        
    }
}
