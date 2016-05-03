<?php
/*
 * This file is part of the GitControlBundle package.
 *
 * (c) Paul Schweppe <paulschweppe@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace VersionControl\GitControlBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use VersionControl\GitControlBundle\Entity\UserProjects;
use VersionControl\GitControlBundle\Entity\Project;
use VersionControl\GitControlBundle\Form\UserProjectsType;
use VersionControl\GitControlBundle\Form\EditUserProjectsType;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * UserProjects controller.
 *
 * @Route("/userprojects")
 */
class UserProjectsController extends Controller
{

    /**
     * Lists all UserProjects entities.
     *
     * @Route("/{id}", name="members_list")
     * @Method("GET")
     * @Template()
     */
    public function membersListAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $project = $em->getRepository('VersionControlGitControlBundle:Project')->find($id);

        if (!$project) {
            throw $this->createNotFoundException('Unable to find Project entity.');
        }
        
        $this->checkProjectAuthorization($project);
       
        $userProjects = $em->getRepository('VersionControlGitControlBundle:UserProjects')->findByProject($project);

        $userProject = new UserProjects();
        $userProject->setProject($project);
        $form   = $this->createCreateForm($userProject,$project);
        $editForm = $this->createEditForm();
        
        return array(
            'userProjects' => $userProjects,
            'project' => $project,
            'form' => $form->createView(),
            'edit_form' => $editForm->createView()
        );
    }
    
    /**
     * Creates a new UserProjects entity.
     *
     * @Route("/{id}", name="userprojects_create")
     * @Method("POST")
     * @Template("VersionControlGitControlBundle:UserProjects:membersList.html.twig")
     */
    public function createAction(Request $request,$id)
    {
        $em = $this->getDoctrine()->getManager();
        
        $project = $em->getRepository('VersionControlGitControlBundle:Project')->find($id);
        if (!$project) {
            throw $this->createNotFoundException('Unable to find Project entity.');
        }
        
        $this->checkProjectAuthorization($project);
        
        $newUserProject = new UserProjects();
        $form = $this->createCreateForm($newUserProject,$project);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em->persist($newUserProject);
            $em->flush();
            
            //$this->createACLSettings($newUserProject);
            
            $this->get('session')->getFlashBag()->add('notice', 'New user has been added to the project');

            return $this->redirect($this->generateUrl('members_list', array('id' => $project->getId())));
        }else{
            $this->get('session')->getFlashBag()->add('notice', 'Error in adding user to this form');
        }

        $userProjects = $em->getRepository('VersionControlGitControlBundle:UserProjects')->findByProject($project);
         
        return array(
            'userProjects' => $userProjects,
            'project' => $project,
            'form' => $form->createView()
        );
    }

    /**
     * Creates a form to create a UserProjects entity.
     *
     * @param UserProjects $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(UserProjects $entity,$project)
    {
        $userProjectsType = new UserProjectsType;
        $form = $this->createForm($userProjectsType->setProjectId($project->getId()), $entity, array(
            'action' => $this->generateUrl('userprojects_create', array('id' => $project->getId())),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Add'));

        return $form;
    }



    /**
    * Creates a form to edit a UserProjects entity.
    *
    * @param UserProjects $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm()
    {

       $form = $this->createFormBuilder(array(), array(
                'method' => 'POST',
           ))
                ->add('roles','choice', array(
                    'label' => 'User Role'
                    ,'choices'  => array('Reporter' => 'Reporter', 'Developer' => 'Developer', 'Master' => 'Master')
                    ,'required' => false
                    ,'empty_value' => 'Please select a role'
                    ,'constraints' => array(
                        new NotBlank()
                    )
                ))
            ->getForm();

        $form->add('submit', 'submit', array('label' => 'Update'));
        return $form;
        
    }
    /**
     * Edits an existing UserProjects entity.
     *
     * @Route("/{id}", name="userprojects_update")
     * @Method("PUT")
     * @Template("VersionControlGitControlBundle:UserProjects:membersList.html.twig")
     */
    public function updateAction(Request $request, $id)
    {

        $em = $this->getDoctrine()->getManager();

        $userProject = $em->getRepository('VersionControlGitControlBundle:UserProjects')->find($id);

        if (!$userProject) {
            throw $this->createNotFoundException('Unable to find UserProjects entity.');
        }
        
        $project = $userProject->getProject();
        $this->checkProjectAuthorization($project);

        $roles = $this->get('request')->request->get('roles');

        if ($roles) {
            $userProject->setRoles($roles);
            $em->flush();
             $this->get('session')->getFlashBag()->add('notice', 'Users role has been updated');
        }else{
            $this->get('session')->getFlashBag()->add('error', 'Error in adding user to this project');
        }
        
        return $this->redirect($this->generateUrl('members_list', array('id' => $project->getId())));
        
    }
    
    /**
     * Deletes a UserProjects entity.
     *
     * @Route("/delete/{id}", name="userprojects_delete")
     */
    public function deleteAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $userProject = $em->getRepository('VersionControlGitControlBundle:UserProjects')->find($id);
        if (!$userProject) {
            throw $this->createNotFoundException('Unable to find UserProjects entity.');
        }
        $project = $userProject->getProject();
        
        $this->checkProjectAuthorization($project);
        $user = $userProject->getUser();
        
        if($project->getCreator()->getId() === $user->getId()){
            throw new \Exception("You cannot delete a user how is the owner of this project");
        }

        $em->remove($userProject);
        $em->flush();
        

        return $this->redirect($this->generateUrl('members_list',array('id'=>$project->getId())));
    }

    
    /**
     * 
     * @param VersionControl\GitControlBundle\Entity\Project $project
     * @throws AccessDeniedException
     */
    protected function checkProjectAuthorization(\VersionControl\GitControlBundle\Entity\Project $project){
        $authorizationChecker = $this->get('security.authorization_checker');

        // check for edit access
        if (false === $authorizationChecker->isGranted('MASTER', $project)) {
            throw new AccessDeniedException();
        }
    }

}
