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

use VersionControl\GitControlBundle\Controller\Base\BaseProjectController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use VersionControl\GitControlBundle\Entity\Project;
use VersionControl\GitControlBundle\Form\ProjectType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use VersionControl\GitControlBundle\Annotation\ProjectAccess;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

/**
 * Project controller.
 *
 * @Route("/project/{id}/branch")
 */
class ProjectBranchController extends BaseProjectController {

    /**
     * List Branches. Not sure how to list remote and local branches.
     *
     * @Route("es/{newBranchName}", name="project_branches")
     * @Method("GET")
     * @Template()
     * @ProjectAccess(grantType="VIEW")
     */
    public function branchesAction($id, $newBranchName = false) {

        $this->initListingView();

        $defaultData = array();
        if ($newBranchName !== false) {
            $defaultData['name'] = $newBranchName;
        }

        $form = $this->createNewBranchForm($this->project, $defaultData);

        return array_merge($this->viewVariables, array(
            'form' => $form->createView(),
            'newBranchName' => $newBranchName,
        ));
    }

    /**
     * Pulls git repository from remote to local.
     *
     * @Route("/create/", name="project_branch")
     * @Method("POST")
     * @Template("VersionControlGitControlBundle:ProjectBranch:branches.html.twig")
     * @ProjectAccess(grantType="EDIT")
     */
    public function createBranchAction(Request $request, $id) {


        $form = $this->createNewBranchForm($this->project);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $newBranchName = $data['name'];
            $switchToBranch = $data['switch'];
            try {

                $response = $this->gitCommands->command('branch')->createLocalBranch($newBranchName, $switchToBranch);
                $this->get('session')->getFlashBag()->add('notice', $response);
                return $this->redirect($this->generateUrl('project_branches', array('id' => $id)));
            } catch (\Exception $e) {
                $this->get('session')->getFlashBag()->add('error', $e->getMessage());
            }
        }

        $this->initListingView();

        return array_merge($this->viewVariables, array(
            'form' => $form->createView(),
            'newBranchName' => false,
        ));
    }

    /**
     * Pulls git repository from remote to local.
     *
     * @Route("/checkoutbranch/{branchName}", name="project_checkoutbranch" , requirements={"branchName"=".+"})
     * @Method("GET")
     * @Template("VersionControlGitControlBundle:Project:branches.html.twig")
     * @ProjectAccess(grantType="EDIT")
     */
    public function checkoutBranchAction($id, $branchName) {

        try {
            $response = $this->gitCommands->command('branch')->checkoutBranch($branchName);

            $this->get('session')->getFlashBag()->add('notice', $response);
        } catch (\Exception $e) {
            $this->get('session')->getFlashBag()->add('error', $e->getMessage());
        }

        return $this->redirect($this->generateUrl('project_branches', array('id' => $id)));
    }

    /**
     * List Branches. Not sure how to list remote and local branches.
     *
     * @Route("/remotes", name="project_branch_remotes")
     * @Method("GET")
     * @Template()
     * @ProjectAccess(grantType="VIEW")
     */
    public function remoteBranchesAction($id) {

        $branchName = $this->gitCommands->command('branch')->getCurrentBranch();

        //Remote Server choice 
        $gitRemoteBranches = $this->gitCommands->command('branch')->getBranchRemoteListing();

        $form = $this->createNewBranchForm($this->project, array(), 'project_branch_remote_checkout');
        $form->add('remotename', 'hidden', array(
            'label' => 'Remote Branch Name'
            , 'required' => true
            , 'constraints' => array(
                new NotBlank()
            ))
        );

        return array_merge($this->viewVariables, array(
            'branches' => $gitRemoteBranches,
            'branchName' => $branchName,
            'form' => $form->createView(),
        ));
    }

    /**
     * Pulls git repository from remote to local.
     *
     * @Route("/checkout-remote", name="project_branch_remote_checkout")
     * @Method("POST")
     * @Template("VersionControlGitControlBundle:ProjectBranch:remoteBranches.html.twig")
     * @ProjectAccess(grantType="EDIT")
     */
    public function checkoutRemoteBranchAction(Request $request, $id) {

        $branchName = $this->gitCommands->command('branch')->getCurrentBranch();
        $gitRemoteBranches = $this->gitCommands->command('branch')->getBranchRemoteListing();

        $form = $this->createNewBranchForm($this->project, array(), 'project_branch_remote_checkout');
        $form->add('remotename', 'hidden', array(
            'label' => 'Remote Branch Name'
            , 'required' => true
            , 'constraints' => array(
                new NotBlank()
            ))
        );

        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $newBranchName = $data['name'];
            $remoteBranchName = $data['remotename'];
            $switchToBranch = $data['switch'];

            try {
                $response = $this->gitCommands->command('branch')->createBranchFromRemote($newBranchName, $remoteBranchName, $switchToBranch);
                $this->get('session')->getFlashBag()->add('notice', $response);
                return $this->redirect($this->generateUrl('project_branch_remotes', array('id' => $id)));
            } catch (\Exception $e) {
                $this->get('session')->getFlashBag()->add('error', $e->getMessage());
            }
        }

        return array_merge($this->viewVariables, array(
            'branches' => $gitRemoteBranches,
            'branchName' => $branchName,
            'form' => $form->createView(),
        ));
    }

    /**
     * Pulls git repository from remote to local.
     *
     * @Route("/fetchall/", name="project_branch_fetchall")
     * @Method("GET")
     * @Template("VersionControlGitControlBundle:ProjectBranch:remoteBranches.html.twig")
     * @ProjectAccess(grantType="EDIT")
     */
    public function fetchAllAction($id) {

        try {
            $response = $this->gitCommands->command('branch')->fetchAll();
            $this->get('session')->getFlashBag()->add('notice', $response);
        } catch (\Exception $e) {
            $this->get('session')->getFlashBag()->add('error', $e->getMessage());
        }
        
        return $this->redirect($this->generateUrl('project_branch_remotes', array('id' => $id)));
    }

    /**
     * Pulls git repository from remote to local.
     *
     * @Route("/deletebranch/{branchName}", name="project_deletebranch" , requirements={"branchName"=".+"})
     * @Method("GET")
     * @Template("VersionControlGitControlBundle:Project:branches.html.twig")
     * @ProjectAccess(grantType="MASTER")
     */
    public function deleteBranchAction($id, $branchName) {

        try {
            $response = $this->gitCommands->command('branch')->deleteBranch($branchName);

            $this->get('session')->getFlashBag()->add('notice', $response);
        }catch (\Exception $e) {
            $this->get('session')->getFlashBag()->add('error', $e->getMessage());
        }
        return $this->redirect($this->generateUrl('project_branches', array('id' => $id)));
    }

    /**
     * Creates a form to edit a Project entity.
     *
     * @param Project $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createNewBranchForm($project, $defaultData = array(), $formAction = 'project_branch') {

        //$defaultData = array();
        $form = $this->createFormBuilder($defaultData, array(
                    'action' => $this->generateUrl($formAction, array('id' => $project->getId())),
                    'method' => 'POST',
                ))
                ->add('name', 'text', array(
                    'label' => 'Branch Name'
                    , 'required' => true
                    , 'constraints' => array(
                        new NotBlank()
                    ))
                )
                ->add('switch', 'checkbox', array(
                    'label' => 'Switch to branch on creation'
                    , 'required' => false
                        )
                )
                ->getForm();

        $form->add('submit', SubmitType::class, array('label' => 'Create'));
        return $form;
    }

    /**
     * Pulls git repository from remote to local.
     *
     * @Route("/mergebranch/{branchName}", name="project_mergebranch" , requirements={"branchName"=".+"})
     * @Method("GET")
     * @Template("VersionControlGitControlBundle:Project:branches.html.twig")
     * @ProjectAccess(grantType="MASTER")
     */
    public function mergeBranchAction($id, $branchName) {
        try {
            $response = $this->gitCommands->command('branch')->mergeBranch($branchName);
            $this->get('session')->getFlashBag()->add('notice', $response);
        }catch (\Exception $e) {
            $this->get('session')->getFlashBag()->add('error', $e->getMessage());
        }
        
        return $this->redirect($this->generateUrl('project_branches'));
    }

    private function getBranchesToMerge() {

        $gitLocalBranches = $this->gitCommands->command('branch')->getBranches(true);
        $currentbranchName = $this->gitCommands->command('branch')->getCurrentBranch();
        $mergeBranches = array();
        foreach ($gitLocalBranches as $branchName) {
            if ($branchName !== $currentbranchName) {
                $mergeBranches[$branchName] = $branchName;
            }
        }

        return $mergeBranches;
    }

    /**
     * Creates a form to edit a Project entity.
     *
     * @param Project $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createMergeBranchForm($project, $branches, $formAction = 'project_mergebranch') {

        $defaultData = array();
        $form = $this->createFormBuilder($defaultData, array(
                    'action' => $this->generateUrl($formAction, array('id' => $project->getId())),
                    'method' => 'POST',
                ))
                ->add('branch', 'choice', array(
                    'choices' => $branches
                    , 'label' => 'Branch Name'
                    , 'required' => true
                    , 'choices_as_values' => true
                    , 'constraints' => array(
                        new NotBlank()
                    ))
                )
                ->getForm();

        $form->add('submit', SubmitType::class, array('label' => 'Merge'));
        return $form;
    }

    protected function initListingView() {

        $branchName = $this->gitCommands->command('branch')->getCurrentBranch();
        //Local Server choice 
        $gitLocalBranches = $this->gitCommands->command('branch')->getBranches(true);

        $gitLogCommand = $this->gitCommands->command('log');


        $gitLogCommand->setBranch($branchName)->setLogCount(1);


        $gitLogs = $gitLogCommand->execute()->getFirstResult();

        //$gitLogs = $this->gitCommands->getLog(1,$branchName);

        $this->viewVariables = array_merge($this->viewVariables, array(
            'branches' => $gitLocalBranches,
            'gitLogs' => array($gitLogs)
        ));
    }

}
