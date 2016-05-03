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
use VersionControl\GitControlBundle\Entity\Issues\IssueInterface;

use VersionControl\GitControlBundle\Entity\Issues\IssueCommentInteface;
use VersionControl\GitControlBundle\Form\IssueCommentType;

use Symfony\Component\HttpFoundation\JsonResponse;

use VersionControl\GitControlBundle\Repository\Issues\IssueRepositoryInterface;
use VersionControl\GitControlBundle\Annotation\ProjectAccess;

/**
 * Issue controller.
 *
 * @Route("project/{id}/issue")
 */
class IssueController extends BaseProjectController
{

    /**
     * 
     * @var IssueRepositoryInterface
     */
    protected $issueRepository;
    
    /**
     *
     * @var \VersionControl\GitControlBundle\Repository\Issues\IssueRepositoryManager;
     */
    protected $issueManager;
    
    /**
     * Lists all Issue entities.
     *
     * @Route("s/", name="issues")
     * @Method("GET")
     * @Template()
     * @ProjectAccess(grantType="VIEW")
     */
    public function indexAction($id, Request $request)
    {

        $keyword = $request->query->get('keyword', false);
        $filter = $request->query->get('filter', 'open');
        
        $data = $this->issueRepository->findIssues($keyword, $filter);
        //$query = $em->getRepository('VersionControlGitControlBundle:Issue')->findByProjectAndStatus($this->project,$filter,$keyword,null,true)->getQuery();

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $data,
            $request->query->getInt('page', 1)/*page number*/,
            15/*limit per page*/
        );
        
        //$openIssuesCount = $em->getRepository('VersionControlGitControlBundle:Issue')->countIssuesForProjectWithStatus($this->project,'open',$keyword);
        //$closedIssuesCount = $em->getRepository('VersionControlGitControlBundle:Issue')->countIssuesForProjectWithStatus($this->project,'closed',$keyword);
        $openIssuesCount = $this->issueRepository->countFindIssues($keyword,'open');
        $closedIssuesCount = $this->issueRepository->countFindIssues($keyword,'closed');
        
        return array_merge($this->viewVariables, array(
            //'entities' => $entities,
            'openIssuesCount' => $openIssuesCount,
            'closedIssuesCount' => $closedIssuesCount,
            'pagination' => $pagination
            
        ));
    }
    
    /**
     * Lists latest Issue entities.
     *
     * @Route("s/latest/", name="issues_latest")
     * @Method("GET")
     * @Template()
     * @ProjectAccess(grantType="VIEW")
     */
    public function latestAction($id, Request $request)
    {
        
        $data = $this->issueRepository->findIssues('', 'open');
        
        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $data,
            $request->query->getInt('page', 1)/*page number*/,
            5/*limit per page*/
        );

        //$openIssuesCount = $em->getRepository('VersionControlGitControlBundle:Issue')->countIssuesForProjectWithStatus($project,'open',false);
        
        return array_merge($this->viewVariables, array(
            //'openIssuesCount' => $openIssuesCount,
            'pagination' => $pagination
            
        ));
    }
    
    
    
    /**
     * Creates a new Issue entity.
     *
     * @Route("/", name="issue_create")
     * @Method("POST")
     * @Template("VersionControlGitControlBundle:Issues/Issue:new.html.twig")
     * @ProjectAccess(grantType="EDIT")
     */
    public function createAction(Request $request,$id)
    {
        
        $issueEntity = $this->issueRepository->newIssue();
        $form = $this->createCreateForm($issueEntity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            
            $issueEntity = $this->issueRepository->createIssue($issueEntity);
            $this->get('session')->getFlashBag()->add('notice', "Issue #".$issueEntity->getId()." successfully updated");

            return $this->redirect($this->generateUrl('issues', array('id' => $this->project->getId())));
        }

        return array_merge($this->viewVariables, array(
            'entity' => $issueEntity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a Issue entity.
     *
     * @param Issue $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(IssueInterface $entity)
    {
        $issueFormType = $this->issueManager->getIssueFormType();
        $form = $this->createForm($issueFormType, $entity, array(
            'action' => $this->generateUrl('issue_create',array('id' => $this->project->getId())),
            'method' => 'POST',
            'data_class' => get_class($entity), // Where we store our entities

        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new Issue entity.
     *
     * @Route("/new/", name="issue_new")
     * @Method("GET")
     * @Template()
     * @ProjectAccess(grantType="EDIT")
     */
    public function newAction($id)
    {
        
        $issueEntity = $this->issueRepository->newIssue();
        $form   = $this->createCreateForm($issueEntity);

        return array_merge($this->viewVariables, array(
            'entity' => $issueEntity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a Issue entity.
     *
     * @Route("/{issueId}", name="issue_show")
     * @Method("GET")
     * @Template()
     * @ProjectAccess(grantType="VIEW")
     */
    public function showAction($id,$issueId)
    {
        
        $issueEntity = $this->issueRepository->findIssueById($issueId);

        if (!$issueEntity) {
            throw $this->createNotFoundException('Unable to find Issue entity.');
        }

        $deleteForm = $this->createDeleteForm($issueId);
        
        //@TODO: Needs to update
        //$issueComment = new IssueComment();
        $issueComment = $this->issueRepository->newIssueComment();
        $issueComment->setIssue($issueEntity);
        $commentForm = $this->createCommentForm($issueComment,$issueId);

        return array_merge($this->viewVariables, array(
            'entity'      => $issueEntity,
            'delete_form' => $deleteForm->createView(),
            'comment_form' => $commentForm->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing Issue entity.
     *
     * @Route("/edit/{issueId}", name="issue_edit")
     * @Method("GET")
     * @Template()
     * @ProjectAccess(grantType="EDIT")
     */
    public function editAction($id,$issueId)
    {
        
        $issueEntity = $this->issueRepository->findIssueById($issueId);  

        $editForm = $this->createEditForm($issueEntity);
        $deleteForm = $this->createDeleteForm($issueId);

        return array_merge($this->viewVariables, array(
            'issue'      => $issueEntity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }

    /**
    * Creates a form to edit a Issue entity.
    *
    * @param Issue $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(IssueInterface $entity)
    {
        $issueFormEditType = $this->issueManager->getIssueEditFormType();
        $form = $this->createForm($issueFormEditType, $entity, array(
            'action' => $this->generateUrl('issue_update', array('id'=>$this->project->getId(),'issueId' => $entity->getId())),
            'method' => 'PUT',
            'data_class' => get_class($entity)
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing Issue entity.
     *
     * @Route("/{issueId}", name="issue_update")
     * @Method("PUT")
     * @Template("VersionControlGitControlBundle:Issues/Issue:edit.html.twig")
     * @ProjectAccess(grantType="EDIT")
     */
    public function updateAction(Request $request, $id, $issueId)
    {
        $issueEntity = $this->issueRepository->findIssueById($issueId);

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($issueEntity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $this->issueRepository->updateIssue($issueEntity);
            
            $this->get('session')->getFlashBag()->add('notice', "Issue #".$issueEntity->getId()." successfully updated");
            
            return $this->redirect($this->generateUrl('issue_edit', array('id'=>$this->project->getId(),'issueId' => $issueId)));
        }

        return array_merge($this->viewVariables, array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        ));
    }
    /**
     * Deletes a Issue entity.
     *
     * @Route("/", name="issue_delete")
     * @Method("DELETE")
     * @ProjectAccess(grantType="OWNER")
     */
    public function deleteAction(Request $request, $id)
    {
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);

        if ($form->isValid()) {

            $issueEntity = $this->issueRepository->findIssueById($issueId);

            if (!$issueEntity) {
                throw $this->createNotFoundException('Unable to find Issue entity.');
            }

        }

        return $this->redirect($this->generateUrl('issue'));
    }
    
    /**
     * Displays a form to edit an existing Issue entity.
     *
     * @Route("/close/{issueId}", name="issue_close")
     * @Method("GET")
     * @ProjectAccess(grantType="EDIT")
     */
    public function closeAction($id,$issueId)
    {
        $issueEntity = $this->issueRepository->closeIssue($issueId);
        
        if (!$issueEntity) {
            throw $this->createNotFoundException('Unable to find Issue entity.');
        }

        $this->get('session')->getFlashBag()->add('notice'
                ,"Issue #".$issueEntity->getId()." has been closed");
        
        return $this->redirect($this->generateUrl('issues', array('id' => $this->project->getId())));
    }
    
     /**
     * Displays a form to edit an existing Issue entity.
     *
     * @Route("/open", name="issue_open")
     * @Method("GET")
      * @ProjectAccess(grantType="EDIT")
     */
    public function openAction($id)
    {
        $issueEntity = $this->issueRepository->reOpenIssue($issueId);
        
        if (!$issueEntity) {
            throw $this->createNotFoundException('Unable to find Issue entity.');
        }

        $this->get('session')->getFlashBag()->add('notice'
                ,"Issue #".$issueEntity->getId()." has been closed");
        
        
        return $this->redirect($this->generateUrl('issue_show', array('id'=>$this->project->getId(),'issueId' => $issueEntity->getId())));

    }
    
    /**
     * Displays a form to edit an existing Issue entity.
     *
     * @Route("/hook/", name="issue_hook")
     * 
     */
    public function hookAction($id)
    {

        $this->gitCommands = $this->get('version_control.git_command')->setProject($this->project);
        $message = $this->gitCommands->getLastMessageLog();
        
        //close|closes|closed|fix|fixes|fixed|resolve|resolves|resolved
        //'/#(\d+)/'
        $matches = array();
        if (preg_match('/(close|closes|closed|fix|fixes|fixed|resolve|resolves|resolved) #(\d+)/i', $message, $matches)) {
            foreach($matches as $issueId){
                if(is_numeric($issueId)){
                     $issueEntity = $this->issueRepository->findIssueById($issueId);
                     if($issue){
                        $this->issueRepository->closeIssue($issueId);
                     }
                }
            }
        }
        
        return new JsonResponse(array('success' => true));
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
    
    
    /**
     * Creates a new Issue comment entity.
     *
     * @Route("/comment/{issueId}", name="issuecomment_create")
     * @Method("POST")
     * @Template("VersionControlGitControlBundle:Issues/Issue:show.html.twig")
     * @ProjectAccess(grantType="EDIT")
     */
    public function createCommentAction(Request $request,$id, $issueId)
    {
        
        $issueComment = $this->issueRepository->newIssueComment();
        $issueEntity = $this->issueRepository->findIssueById($issueId);

        $commentForm = $this->createCommentForm($issueComment,$issueId);
        $commentForm->handleRequest($request);

        if ($commentForm->isValid()) {
            $issueComment->setIssue($issueEntity);
            $issueComment = $this->issueRepository->createIssueComment($issueComment);
            //$issueId = $issueComment->getIssue()->getId();
            
            if($commentForm->get('createClose')->isClicked()){
                $this->issueRepository->closeIssue($issueId);
            }
            
           // $em->persist($entity);
           // $em->flush();

            return $this->redirect($this->generateUrl('issue_show', array('id'=>$this->project->getId(),'issueId' => $issueId)));
        }
        
        $deleteForm = $this->createDeleteForm($issueId);

        return array_merge($this->viewVariables, array(
            'entity'      => $issueEntity,
            'delete_form' => $deleteForm->createView(),
            'comment_form' => $commentForm->createView(),

        ));
    }
    
    /**
     * Creates a form to create a Issue entity.
     *
     * @param Issue $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCommentForm(IssueCommentInteface $entity, $issueId)
    {
        $issueCommentType = $this->issueManager->getIssueCommentFormType();
        $form = $this->createForm($issueCommentType, $entity, array(
            'action' => $this->generateUrl('issuecomment_create',array('id'=>$this->project->getId(),'issueId' => $issueId)),
            'method' => 'POST',
            'data_class' => get_class($entity),
        ));

        $form->add('create', 'submit', array('label' => 'Create'));
        $form->add('createClose', 'submit', array('label' => 'Create & Close'));

        return $form;
    }
    
    /**
     * Displays a form to edit an existing Issue entity.
     *
     * @Route("s/search/", name="issue_searchajax")
     * @Method("GET")
     * @ProjectAccess(grantType="VIEW")
     */
    public function searchAjaxAction($id,Request $request)
    {
        
        $keyword = $request->query->get('keyword', false);
        $filter = $request->query->get('filter', 'open');
        
        if($keyword && is_numeric($keyword)){
            $data = array();
            //Get issue by id
            try{
                $issue = $this->issueRepository->findIssueById($keyword);
                if($issue){
                    $data[] = $issue;
                }
            }catch(\Exception $e){
                //return new JsonResponse(array('success' => false, 'error' => $result));
            }
        }else{
            $data = $this->issueRepository->findIssues($keyword, $filter);
        }
        //$issueEntities = $em->getRepository('VersionControlGitControlBundle:Issue')->findByProjectAndStatus($project,$filter,$keyword,null,false);
        
        if($data instanceof \Doctrine\ORM\QueryBuilder){
            
            $issueEntities = $data->getQuery()->getResult();
        }else{
            $issueEntities = $data;
        }
        $result = [];
        foreach($issueEntities as $issueEntity){
            $result[] = array(
                'id' => $issueEntity->getId()
                ,'title' => $issueEntity->getTitle()
                ,'description' => $issueEntity->getDescription() 
                ,'status' => $issueEntity->getStatus()
                ,'author' => $issueEntity->getUser()->getName()
            );
        }
        
        return new JsonResponse(array('success' => true, 'results' => $result));
    }
    
    /**
     * Displays a form to edit an existing Issue entity.
     *
     * @Route("/find/{issueId}", name="issue_findajax")
     * @Method("GET")
     * @TODO Pass in project id
     * @ProjectAccess(grantType="VIEW")
     */
    public function findAjaxAction($id,$issueId = '')
    {
        //$em = $this->getDoctrine()->getManager();
        //$issueEntity = $em->getRepository('VersionControlGitControlBundle:Issue')->find($issueId);
        $issueEntity = $this->issueRepository->findIssueById($issueId);
        if (!$issueEntity) {
            return new JsonResponse(array('success' => false, 'error' => 'No issue exists matching this id.'));
        }
        $result = array(
            'id' => $issueEntity->getId()
            ,'title' => $issueEntity->getTitle()
            ,'description' => $issueEntity->getDescription() 
            ,'status' => $issueEntity->getStatus()
        );
        
        return new JsonResponse(array('success' => true, 'result' => $result));

    }
    
    /**
     * Finds and displays a Issue entity.
     *
     * @Route("/modal/{issueId}", name="issue_show_modal")
     * @Method("GET")
     * @Template()
     * @ProjectAccess(grantType="VIEW")
     */
    public function showModalAction($id,$issueId)
    {
        
        $issueEntity = $this->issueRepository->findIssueById($issueId);

        if (!$issueEntity) {
            throw $this->createNotFoundException('Unable to find Issue entity.');
        }

        return array(
            'entity'  => $issueEntity,
            'project' => $this->project,
        );
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
        $this->issueRepository = $this->issueManager->getIssueRepository();
        
    }

}
