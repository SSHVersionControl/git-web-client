<?php

namespace VersionContol\GitControlBundle\Controller\Issues;

use Symfony\Component\HttpFoundation\Request;
use VersionContol\GitControlBundle\Controller\Base\BaseProjectController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use VersionContol\GitControlBundle\Entity\Issue;
use VersionContol\GitControlBundle\Form\IssueType;
use VersionContol\GitControlBundle\Form\IssueEditType;

use VersionContol\GitControlBundle\Entity\IssueComment;
use VersionContol\GitControlBundle\Form\IssueCommentType;

use Symfony\Component\HttpFoundation\JsonResponse;

use VersionContol\GitControlBundle\Repository\Issues\IssueRepositoryInterface;

/**
 * Issue controller.
 *
 * @Route("/issue")
 */
class IssueController extends BaseProjectController
{

    /**
     * 
     * @var IssueRepositoryInterface
     */
    protected $issueRepository;
    
    /**
     * Lists all Issue entities.
     *
     * @Route("s/{id}", name="issues")
     * @Method("GET")
     * @Template()
     */
    public function indexAction($id, Request $request)
    {

        $this->initAction($id);
        
        $keyword = $request->query->get('keyword', false);
        $filter = $request->query->get('filter', 'open');
        
        $data = $this->issueRepository->findIssues($keyword, $filter);
        //$query = $em->getRepository('VersionContolGitControlBundle:Issue')->findByProjectAndStatus($this->project,$filter,$keyword,null,true)->getQuery();

        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $data,
            $request->query->getInt('page', 1)/*page number*/,
            15/*limit per page*/
        );
        
        //$openIssuesCount = $em->getRepository('VersionContolGitControlBundle:Issue')->countIssuesForProjectWithStatus($this->project,'open',$keyword);
        //$closedIssuesCount = $em->getRepository('VersionContolGitControlBundle:Issue')->countIssuesForProjectWithStatus($this->project,'closed',$keyword);
        $openIssuesCount = $this->issueRepository->countFindIssues($keyword,'open');
        $closedIssuesCount = $this->issueRepository->countFindIssues($keyword,'closed');
        
        return array(
            //'entities' => $entities,
            'project' => $this->project,
            'openIssuesCount' => $openIssuesCount,
            'closedIssuesCount' => $closedIssuesCount,
            'pagination' => $pagination
            
        );
    }
    
    /**
     * Lists latest Issue entities.
     *
     * @Route("s/latest/{id}", name="issues_latest")
     * @Method("GET")
     * @Template()
     */
    public function latestAction($id, Request $request)
    {
        $this->initAction($id);
        
        $data = $this->issueRepository->findIssues('', 'open');
        
        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $data,
            $request->query->getInt('page', 1)/*page number*/,
            5/*limit per page*/
        );

        //$openIssuesCount = $em->getRepository('VersionContolGitControlBundle:Issue')->countIssuesForProjectWithStatus($project,'open',false);
        
        return array(
            //'entities' => $entities,
            'project' => $this->project,
            //'openIssuesCount' => $openIssuesCount,
            'pagination' => $pagination
            
        );
    }
    
    
    
    /**
     * Creates a new Issue entity.
     *
     * @Route("/{id}", name="issue_create")
     * @Method("POST")
     * @Template("VersionContolGitControlBundle:Issues/Issue:new.html.twig")
     */
    public function createAction(Request $request,$id)
    {
        $this->initAction($id,'EDIT');
        
        $issueEntity = $this->issueRepository->newIssue();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            
            $issueEntity = $this->issueRepository->createIssue($issueEntity);
            $this->get('session')->getFlashBag()->add('notice', "Issue #".$issueEntity->getId()." successfully updated");

            return $this->redirect($this->generateUrl('issues', array('id' => $this->project->getId())));
        }

        return array(
            'entity' => $issueEntity,
            'form'   => $form->createView(),
            'project'   => $this->project,
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
            'action' => $this->generateUrl('issue_create',array('id' => $this->project->getId())),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new Issue entity.
     *
     * @Route("/new/{id}", name="issue_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction($id)
    {
        $this->initAction($id);
        
        $entity = $this->issueRepository->newIssue();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'project' => $this->project
        );
    }

    /**
     * Finds and displays a Issue entity.
     *
     * @Route("/{id}/{issueId}", name="issue_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id,$issueId)
    {
        $this->initAction($id);
        
        $issueEntity = $this->issueRepository->findIssueById($issueId);

        if (!$issueEntity) {
            throw $this->createNotFoundException('Unable to find Issue entity.');
        }

        $deleteForm = $this->createDeleteForm($issueId);
        
        //@TODO: Needs to update
        $issueComment = new IssueComment();
        $issueComment->setIssue($issueEntity);
        $commentForm = $this->createCommentForm($issueComment);

        return array(
            'entity'      => $issueEntity,
            'delete_form' => $deleteForm->createView(),
            'project' => $this->project,
            'comment_form' => $commentForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Issue entity.
     *
     * @Route("/{id}/edit/{issueId}", name="issue_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id,$issueId)
    {
        $this->initAction($id,'EDIT');
        
        $issueEntity = $this->issueRepository->findIssueById($issueId);  

        $editForm = $this->createEditForm($issueEntity);
        $deleteForm = $this->createDeleteForm($issueId);

        return array(
            'issue'      => $issueEntity,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'project' => $this->project,
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
        $form = $this->createForm(new IssueEditType(), $entity, array(
            'action' => $this->generateUrl('issue_update', array('id'=>$this->project->getId(),'issueId' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing Issue entity.
     *
     * @Route("/{id}/{issueId}", name="issue_update")
     * @Method("PUT")
     * @Template("VersionContolGitControlBundle:Issues/Issue:edit.html.twig")
     */
    public function updateAction(Request $request, $id, $issueId)
    {
        $this->initAction($id,'EDIT');
        $issueEntity = $this->issueRepository->findIssueById($issueId);

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($issueEntity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $this->issueRepository->updateIssue($issueEntity);
            
            $this->get('session')->getFlashBag()->add('notice', "Issue #".$issueEntity->getId()." successfully updated");
            
            return $this->redirect($this->generateUrl('issue_edit', array('id'=>$this->project->getId(),'issueId' => $issueId)));
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
     * @Route("/{id}/close/{issueId}", name="issue_close")
     * @Method("GET")
     */
    public function closeAction($id,$issueId)
    {
        $this->initAction($id,'EDIT');
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
     * @Route("/{id}/open", name="issue_open")
     * @Method("GET")
     */
    public function openAction($id)
    {
        $this->initAction($id,'EDIT');
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
     * @Route("/hook/{id}", name="issue_hook")
     * 
     */
    public function hookAction($id)
    {
        $this->initAction($id,'EDIT');

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
     * @Route("/comment/", name="issuecomment_create")
     * @Method("POST")
     * @Template("VersionContolGitControlBundle:Issues/Issue:show.html.twig")
     */
    public function createCommentAction(Request $request)
    {
        $entity = new IssueComment();
        $commentForm = $this->createCommentForm($entity);
        $commentForm->handleRequest($request);

        if ($commentForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            
            $project = $entity->getIssue()->getProject();
            $this->checkProjectAuthorization($project,'EDIT');
            
            //Set User
            $user = $this->get('security.token_storage')->getToken()->getUser();
            $entity->setVerUser($user);
            
            if($commentForm->get('createClose')->isClicked()){
                $entity->getIssue()->setClosed();
            }
            
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('issue_show', array('id'=>$this->project->getId(),'issueId' => $entity->getIssue()->getId())));
        }
        
        $deleteForm = $this->createDeleteForm($entity->getIssue()->getId());

        return array(
            'entity'      => $entity->getIssue(),
            'delete_form' => $deleteForm->createView(),
            'project' => $entity->getIssue()->getProject(),
            'comment_form' => $commentForm->createView(),

        );
    }
    
    /**
     * Creates a form to create a Issue entity.
     *
     * @param Issue $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCommentForm(IssueComment $entity)
    {
        $form = $this->createForm(new IssueCommentType(), $entity, array(
            'action' => $this->generateUrl('issuecomment_create'),
            'method' => 'POST',
        ));

        $form->add('create', 'submit', array('label' => 'Create'));
        $form->add('createClose', 'submit', array('label' => 'Create & Close'));

        return $form;
    }
    
    /**
     * Displays a form to edit an existing Issue entity.
     *
     * @Route("s/search/{id}", name="issue_searchajax")
     * @Method("GET")
     */
    public function searchAjaxAction($id,Request $request)
    {
        $this->initAction($id);
        
        $keyword = $request->query->get('keyword', false);
        $filter = $request->query->get('filter', 'open');
        
        //$issueEntities = $em->getRepository('VersionContolGitControlBundle:Issue')->findByProjectAndStatus($project,$filter,$keyword,null,false);
        $data = $this->issueRepository->findIssues($keyword, $filter);
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
     */
    public function findAjaxAction($issueId)
    {
        $em = $this->getDoctrine()->getManager();
        $issueEntity = $em->getRepository('VersionContolGitControlBundle:Issue')->find($issueId);
        
        if (!$issueEntity) {
            throw $this->createNotFoundException('Unable to find issue entity.');
        }
        
        $this->checkProjectAuthorization($issueEntity->getProject(),'VIEW');
    }
    
    /**
     * Finds and displays a Issue entity.
     *
     * @Route("/{id}/modal/{issueId}", name="issue_show_modal")
     * @Method("GET")
     * @Template()
     */
    public function showModalAction($id,$issueId)
    {
        $this->initAction($id);
        
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
    protected function initAction($id,$grantType='VIEW'){
        $em = $this->getDoctrine()->getManager();
        
        $this->project= $em->getRepository('VersionContolGitControlBundle:Project')->find($id);

        if (!$this->project) {
            throw $this->createNotFoundException('Unable to find Project entity.');
        }
        
        $this->checkProjectAuthorization($this->project,$grantType);
        
        $this->issueRepository = $this->getIssueRepository($this->project);
        
    }
    
    protected function getIssueRepository($project){
        $issueRepository = null;
        $em = $this->getDoctrine()->getManager();

        $issueIntegrator= $em->getRepository('VersionContolGitControlBundle:ProjectIssueIntegrator')->findOneByProject($project);

        if($issueIntegrator){ 
            $repoType = $issueIntegrator->getRepoType();
            $issueRepository = $this->get('version_control.repository.'.strtolower($repoType));
            $issueRepository->setIssueIntegrator($issueIntegrator);
        }else{
            //Default ORM repository
            $issueRepository = $em->getRepository('VersionContolGitControlBundle:Issue');
            $issueRepository->setProject($this->project);
            //Set User
            $user = $this->get('security.token_storage')->getToken()->getUser();
            $issueRepository->setCurrentUser($user);
        }
        
        return $issueRepository;
    }
}
