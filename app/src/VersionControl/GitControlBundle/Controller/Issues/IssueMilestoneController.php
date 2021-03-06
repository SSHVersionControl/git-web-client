<?php
/*
 * This file is part of the GitControlBundle package.
 *
 * (c) Paul Schweppe <paulschweppe@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace VersionControl\GitControlBundle\Controller\Issues;

use Symfony\Component\HttpFoundation\Request;
use VersionControl\GitControlBundle\Controller\Base\BaseProjectController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use VersionControl\GitControlBundle\Entity\Issues\IssueMilestoneInterface;
use VersionControl\GitControlBundle\Annotation\ProjectAccess;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

/**
 * IssueMilestone controller.
 *
 * @Route("project/{id}/issuemilestone")
 */
class IssueMilestoneController extends BaseProjectController
{
    /**
     * @var IssueMilestoneRepositoryInterface
     */
    protected $issueMilestoneRepository;

    /**
     * @var \VersionControl\GitControlBundle\Repository\Issues\IssueRepositoryManager;
     */
    protected $issueManager;

    /**
     * Lists all IssueMilestone entities.
     *
     * @Route("s/", name="issuemilestones")
     * @Method("GET")
     * @Template()
     * @ProjectAccess(grantType="VIEW")
     */
    public function indexAction($id, Request $request)
    {
        $filter = $request->query->get('filter', 'open');

        $data = $this->issueMilestoneRepository->listMilestones($filter);

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $data,
            $request->query->getInt('page', 1)/*page number*/,
            15/*limit per page*/
        );

        $openCount = $this->issueMilestoneRepository->countMilestones('open');
        $closedCount = $this->issueMilestoneRepository->countMilestones('closed');

        return array_merge($this->viewVariables, array(
            'openCount' => $openCount,
            'closedCount' => $closedCount,
            'pagination' => $pagination,
        ));
    }
    /**
     * Creates a new IssueMilestone entity.
     *
     * @Route("/", name="issuemilestone_create")
     * @Method("POST")
     * @Template("VersionControlGitControlBundle:Issues/IssueMilestone:new.html.twig")
     * @ProjectAccess(grantType="VIEW")
     */
    public function createAction(Request $request, $id)
    {
        $issueMilestone = $this->issueMilestoneRepository->newMilestone();
        $form = $this->createCreateForm($issueMilestone);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $issueMilestone = $this->issueMilestoneRepository->createMilestone($issueMilestone);

            $this->get('session')->getFlashBag()->add('notice', 'New Milestone:'.$issueMilestone->getTitle());

            return $this->redirect($this->generateUrl('issuemilestone_show', array('milestoneId' => $issueMilestone->getId())));
        }

        return array_merge($this->viewVariables, array(
            'entity' => $issueMilestone,
            'form' => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a IssueMilestone entity.
     *
     * @param IssueMilestone $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(IssueMilestoneInterface $entity)
    {
        $issueLMilestoneFormType = $this->issueManager->getIssueMilestoneFormType();
        $form = $this->createForm($issueLMilestoneFormType, $entity, array(
            'action' => $this->generateUrl('issuemilestone_create', array('id' => $this->project->getId())),
            'method' => 'POST',
        ));

        $form->add('submit', SubmitType::class, array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new IssueMilestone entity.
     *
     * @Route("/new/", name="issuemilestone_new")
     * @Method("GET")
     * @Template()
     * @ProjectAccess(grantType="VIEW")
     */
    public function newAction($id)
    {
        $issueMilestone = $this->issueMilestoneRepository->newMilestone();
        $form = $this->createCreateForm($issueMilestone);

        return array_merge($this->viewVariables, array(
            'entity' => $issueMilestone,
            'form' => $form->createView(),
        ));
    }

    /**
     * Finds and displays a IssueMilestone entity.
     *
     * @Route("/{milestoneId}", name="issuemilestone_show")
     * @Method("GET")
     * @Template()
     * @ProjectAccess(grantType="VIEW")
     */
    public function showAction($id, $milestoneId)
    {
        $issueMilestone = $this->issueMilestoneRepository->findMilestoneById($milestoneId);

        if (!$issueMilestone) {
            throw $this->createNotFoundException('Unable to find IssueMilestone entity.');
        }

        $deleteForm = $this->createDeleteForm($id);

        $issueRepository = $this->issueManager->getIssueRepository();

        $openIssuesCount = $issueRepository->countIssuesInMilestones($milestoneId, 'open');
        $closedIssuesCount = $issueRepository->countIssuesInMilestones($milestoneId, 'closed');

        return array_merge($this->viewVariables, array(
            'entity' => $issueMilestone,
            'delete_form' => $deleteForm->createView(),
            'openCount' => $openIssuesCount,
            'closedCount' => $closedIssuesCount,
        ));
    }

    /**
     * Displays a form to edit an existing IssueMilestone entity.
     *
     * @Route("/edit/{milestoneId}", name="issuemilestone_edit")
     * @Method("GET")
     * @Template()
     * @ProjectAccess(grantType="VIEW")
     */
    public function editAction($id, $milestoneId)
    {
        $issueMilestone = $this->issueMilestoneRepository->findMilestoneById($milestoneId);

        if (!$issueMilestone) {
            throw $this->createNotFoundException('Unable to find IssueMilestone entity.');
        }

        $editForm = $this->createEditForm($issueMilestone);
        $deleteForm = $this->createDeleteForm($id);

        return array_merge($this->viewVariables, array(
            'milestone' => $issueMilestone,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
     * Creates a form to edit a IssueMilestone entity.
     *
     * @param IssueMilestone $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createEditForm(IssueMilestoneInterface $entity)
    {
        $issueLMilestoneFormType = $this->issueManager->getIssueMilestoneFormType();
        $form = $this->createForm($issueLMilestoneFormType, $entity, array(
            'action' => $this->generateUrl('issuemilestone_update', array('id' => $this->project->getId(), 'milestoneId' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', SubmitType::class, array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing IssueMilestone entity.
     *
     * @Route("/{milestoneId}", name="issuemilestone_update")
     * @Method("PUT")
     * @Template("VersionControlGitControlBundle:Issues/IssueMilestone:edit.html.twig")
     * @ProjectAccess(grantType="VIEW")
     */
    public function updateAction(Request $request, $id, $milestoneId)
    {
        $issueMilestone = $this->issueMilestoneRepository->findMilestoneById($milestoneId);

        if (!$issueMilestone) {
            throw $this->createNotFoundException('Unable to find IssueMilestone entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($issueMilestone);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $this->issueMilestoneRepository->updateMilestone($issueMilestone);
            $this->get('session')->getFlashBag()->add('notice', 'Updated Milestone:'.$issueMilestone->getTitle());

            return $this->redirect($this->generateUrl('issuemilestone_show', array('id' => $id, 'milestoneId' => $milestoneId)));
        }

        return array_merge($this->viewVariables, array(
            'entity' => $issueMilestone,
            'edit_form' => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a IssueMilestone entity.
     *
     * @Route("/{milestoneId}", name="issuemilestone_delete")
     * @Method("DELETE")
     * @ProjectAccess(grantType="OWNER")
     */
    public function deleteAction(Request $request, $id, $milestoneId)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->issueMilestoneRepository->deleteMilestone($milestoneId);

            $this->get('session')->getFlashBag()->add('notice', 'Deleted Milestone:'.$milestoneId);
        }

        return $this->redirect($this->generateUrl('issuemilestones', array('id' => $id)));
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
            ->setAction($this->generateUrl('issuemilestone_delete', array('id' => $this->project->getId(), 'milestoneId' => $id)))
            ->setMethod('DELETE')
            ->add('submit', SubmitType::class, array('label' => 'Delete'))
            ->getForm()
        ;
    }

    /**
     * Displays a form to edit an existing Issue entity.
     *
     * @Route("/close/{milestoneId}", name="issuemilestone_close")
     * @Method("GET")
     * @ProjectAccess(grantType="EDIT")
     */
    public function closeAction($id, $milestoneId)
    {
        $issueMilestone = $this->issueMilestoneRepository->closeMilestone($milestoneId);

        if (!$issueMilestone) {
            throw $this->createNotFoundException('Unable to find Issue Milestone entity.');
        }

        $this->get('session')->getFlashBag()->add('notice', 'Milestone #'.$issueMilestone->getId().' has been closed');

        return $this->redirect($this->generateUrl('issuemilestones', array('project' => $issueMilestone->getProject()->getId())));
    }

    /**
     * Displays a form to edit an existing Issue entity.
     *
     * @Route("/open/{milestoneId}", name="issuemilestone_open")
     * @Method("GET")
     * @ProjectAccess(grantType="EDIT")
     */
    public function openAction($id, $milestoneId)
    {
        $issueMilestone = $this->issueMilestoneRepository->reOpenMilestone($milestoneId);

        if (!$issueMilestone) {
            throw $this->createNotFoundException('Unable to find Issue Milestone entity.');
        }

        $this->get('session')->getFlashBag()->add('notice', 'Milestone #'.$issueMilestone->getId().' has been opened');

        return $this->redirect($this->generateUrl('issuemilestone_show', array('id' => $this->project->getId(), 'milestoneId' => $issueMilestone->getId())));
    }

    /**
     * Lists all Issue entities.
     *
     * @Template()
     */
    public function milestonesIssuesAction(Request $request, $id, $issueMilestone, $filter = 'open', $pageParameterName = 'page', $keywordParamaterName = 'keyword')
    {
        $em = $this->getDoctrine()->getManager();

        $this->project = $em->getRepository('VersionControlGitControlBundle:Project')->find($id);

        if (!$this->project) {
            throw $this->createNotFoundException('Unable to find Project entity.');
        }

        $this->initIssueMilestoneRepository($this->project);

        $parentRequest = $request->createFromGlobals();
        $keyword = $parentRequest->query->get($keywordParamaterName, false);

        $issueRepository = $this->issueManager->getIssueRepository();
        $data = $issueRepository->findIssuesInMilestones($issueMilestone->getId(), $filter, $keyword);

        //$data = $em->getRepository('VersionControlGitControlBundle:Issue')->findByProjectAndStatus($issueMilestone->getProject(),$filter,$keyword,$issueMilestone,true)->getQuery();
        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $data,
            $parentRequest->query->getInt($pageParameterName, 1)/*page number*/,
            10, /*limit per page*/
            array('pageParameterName' => $pageParameterName)
        );

        return array_merge($this->viewVariables, array(
            'issueMilestone' => $issueMilestone,
            'pagination' => $pagination,
            'status' => $filter,
            'keywordParamaterName' => $keywordParamaterName,
            'keyword' => $keyword,
            'project' => $this->project,

        ));
    }

    /**
     * @param int $id
     */
    public function initAction($id, $grantType = 'VIEW')
    {
        $em = $this->getDoctrine()->getManager();

        $redirectUrl = parent::initAction($id, $grantType);
        if ($redirectUrl) {
            return $redirectUrl;
        }

        $issueIntegrator = $em->getRepository('VersionControlGitControlBundle:ProjectIssueIntegrator')->findOneByProject($this->project);

        $this->issueManager = $this->get('version_control.issue_repository_manager');
        if ($issueIntegrator) {
            $this->issueManager->setIssueIntegrator($issueIntegrator);
        } else {
            $this->issueManager->setProject($this->project);
        }
        $this->issueMilestoneRepository = $this->issueManager->getIssueMilestoneRepository();
    }

    protected function initIssueMilestoneRepository($project)
    {
        $em = $this->getDoctrine()->getManager();

        $issueIntegrator = $em->getRepository('VersionControlGitControlBundle:ProjectIssueIntegrator')->findOneByProject($project);

        $this->issueManager = $this->get('version_control.issue_repository_manager');
        if ($issueIntegrator) {
            $this->issueManager->setIssueIntegrator($issueIntegrator);
        } else {
            $this->issueManager->setProject($project);
        }
        $this->issueMilestoneRepository = $this->issueManager->getIssueMilestoneRepository();
    }
}
