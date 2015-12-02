<?php

namespace VersionContol\GitControlBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use VersionContol\GitControlBundle\Entity\Issue;
use VersionContol\GitControlBundle\Form\IssueType;
use VersionContol\GitControlBundle\Form\IssueEditType;

use VersionContol\GitControlBundle\Entity\IssueComment;
use VersionContol\GitControlBundle\Form\IssueCommentType;

use Symfony\Component\HttpFoundation\JsonResponse;

/**
 * Issue controller.
 *
 * @Route("/issue")
 */
class IssueController extends Controller
{

    /**
     * Lists all Issue entities.
     *
     * @Route("s/{id}", name="issues")
     * @Method("GET")
     * @Template()
     */
    public function indexAction($id, Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        
        $project= $em->getRepository('VersionContolGitControlBundle:Project')->find($id);

        if (!$project) {
            throw $this->createNotFoundException('Unable to find Project entity.');
        }
        
        //$this->checkProjectAuthorization($project,'EDIT');
        

        //$entities = $em->getRepository('VersionContolGitControlBundle:Issue')->findByProject($project);
        $keyword = $request->query->get('keyword', false);
        $filter = $request->query->get('filter', false);
        
        $query = $em->getRepository('VersionContolGitControlBundle:Issue')->findByProjectAndStatus($project,$filter,$keyword,true)->getQuery();
        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1)/*page number*/,
            15/*limit per page*/
        );

    
        $openIssuesCount = $em->getRepository('VersionContolGitControlBundle:Issue')->countIssuesForProjectWithStatus($project,'open',$keyword);
        $closedIssuesCount = $em->getRepository('VersionContolGitControlBundle:Issue')->countIssuesForProjectWithStatus($project,'closed',$keyword);
        
        return array(
            //'entities' => $entities,
            'project' => $project,
            'openIssuesCount' => $openIssuesCount,
            'closedIssuesCount' => $closedIssuesCount,
            'pagination' => $pagination
            
        );
    }
    
    /**
     * Creates a new Issue entity.
     *
     * @Route("/", name="issue_create")
     * @Method("POST")
     * @Template("VersionContolGitControlBundle:Issue:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $entity = new Issue();
        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            
            //Set User
            $user = $this->get('security.token_storage')->getToken()->getUser();
            $entity->setVerUser($user);
            
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('issues', array('id' => $entity->getProject()->getId())));
        }

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'project'   => $entity->getProject(),
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
            'action' => $this->generateUrl('issue_create'),
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
        $em = $this->getDoctrine()->getManager();
        
        $project= $em->getRepository('VersionContolGitControlBundle:Project')->find($id);

        if (!$project) {
            throw $this->createNotFoundException('Unable to find Project entity.');
        }
        
        
        $entity = new Issue();
        $entity->setProject($project);
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
            'project' => $project
        );
    }

    /**
     * Finds and displays a Issue entity.
     *
     * @Route("/{id}", name="issue_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('VersionContolGitControlBundle:Issue')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Issue entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        
        $issueComment = new IssueComment();
        $issueComment->setIssue($entity);
        $commentForm = $this->createCommentForm($issueComment);

        return array(
            'entity'      => $entity,
            'delete_form' => $deleteForm->createView(),
            'project' => $entity->getProject(),
            'comment_form' => $commentForm->createView(),
        );
    }

    /**
     * Displays a form to edit an existing Issue entity.
     *
     * @Route("/{id}/edit", name="issue_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $issue = $em->getRepository('VersionContolGitControlBundle:Issue')->find($id);

        if (!$issue) {
            throw $this->createNotFoundException('Unable to find Issue entity.');
        }

        $editForm = $this->createEditForm($issue);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'issue'      => $issue,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
            'project' => $issue->getProject(),
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
            'action' => $this->generateUrl('issue_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing Issue entity.
     *
     * @Route("/{id}", name="issue_update")
     * @Method("PUT")
     * @Template("VersionContolGitControlBundle:Issue:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('VersionContolGitControlBundle:Issue')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Issue entity.');
        }

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('issue_edit', array('id' => $id)));
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
            $em = $this->getDoctrine()->getManager();
            $entity = $em->getRepository('VersionContolGitControlBundle:Issue')->find($id);

            if (!$entity) {
                throw $this->createNotFoundException('Unable to find Issue entity.');
            }

            $em->remove($entity);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('issue'));
    }
    
    /**
     * Displays a form to edit an existing Issue entity.
     *
     * @Route("/{id}/close", name="issue_close")
     * @Method("GET")
     */
    public function closeAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $issue = $em->getRepository('VersionContolGitControlBundle:Issue')->find($id);

        if (!$issue) {
            throw $this->createNotFoundException('Unable to find Issue entity.');
        }
        
        $issue->setClosed();
        $em->flush();
        
        $this->get('session')->getFlashBag()->add('notice'
                ,"Issue #".$issue->getId()." has been closed");
        
        return $this->redirect($this->generateUrl('issues', array('id' => $issue->getProject()->getId())));
    }
    
     /**
     * Displays a form to edit an existing Issue entity.
     *
     * @Route("/{id}/open", name="issue_open")
     * @Method("GET")
     */
    public function openAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $issue = $em->getRepository('VersionContolGitControlBundle:Issue')->find($id);

        if (!$issue) {
            throw $this->createNotFoundException('Unable to find Issue entity.');
        }
        
        $issue->setOpen();
        $em->flush();
        
        $this->get('session')->getFlashBag()->add('notice'
                ,"Issue #".$issue->getId()." has been opened");
        
        return $this->redirect($this->generateUrl('issue_show', array('id' => $issue->getId())));

    }
    
    /**
     * Displays a form to edit an existing Issue entity.
     *
     * @Route("/hook/{id}", name="issue_hook")
     * 
     */
    public function hookAction($id)
    {
  
        $em = $this->getDoctrine()->getManager();
        $project = $em->getRepository('VersionContolGitControlBundle:Project')->find($id);

        if (!$project) {
            throw $this->createNotFoundException('Unable to find project entity.');
        }
        
        $this->gitCommands = $this->get('version_control.git_command')->setProject($project);
        $message = $this->gitCommands->getLastMessageLog();
        
        //close|closes|closed|fix|fixes|fixed|resolve|resolves|resolved
        //'/#(\d+)/'
        $matches = array();
        if (preg_match('/(close|closes|closed|fix|fixes|fixed|resolve|resolves|resolved) #(\d+)/i', $message, $matches)) {
            foreach($matches as $issueId){
                if(is_numeric($issueId)){
                     $issue = $em->getRepository('VersionContolGitControlBundle:Issue')->find($issueId);
                     if($issue){
                        if($issue->getProject()->getId() === $project->getId()){
                           $issue->setClosed();
                        }
                     }
                }
            }
            $em->flush();
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
     * @Template("VersionContolGitControlBundle:Issue:show.html.twig")
     */
    public function createCommentAction(Request $request)
    {
        $entity = new IssueComment();
        $commentForm = $this->createCommentForm($entity);
        $commentForm->handleRequest($request);

        if ($commentForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            
            //Set User
            $user = $this->get('security.token_storage')->getToken()->getUser();
            $entity->setVerUser($user);
            
            if($commentForm->get('createClose')->isClicked()){
                $entity->getIssue()->setClosed();
            }
            
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('issue_show', array('id' => $entity->getIssue()->getId())));
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
}
