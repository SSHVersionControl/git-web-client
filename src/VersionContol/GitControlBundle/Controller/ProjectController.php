<?php

namespace VersionContol\GitControlBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use VersionContol\GitControlBundle\Entity\Project;
use VersionContol\GitControlBundle\Form\ProjectType;
use VersionContol\GitControlBundle\Utility\GitCommands;
use Symfony\Component\Validator\Constraints\NotBlank;
use VersionContol\GitControlBundle\Entity\UserProjects;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Project controller.
 *
 * @Route("/project")
 */
class ProjectController extends Controller
{

    /**
     * Lists all Project entities.
     *
     * @Route("/", name="project")
     * @Method("GET")
     * @Template()
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('VersionContolGitControlBundle:Project')->findAll();

        return array(
            'entities' => $entities,
        );
    }
    
    
    /**
     * Creates a new Project entity.
     *
     * @Route("/", name="project_create")
     * @Method("POST")
     * @Template("VersionContolGitControlBundle:Project:new.html.twig")
     */
    public function createAction(Request $request)
    {
        $project = new Project();
        $form = $this->createCreateForm($project);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            
            //Get User
            $user = $this->get('security.token_storage')->getToken()->getUser();
            
            //Set Creator
            $project->setCreator($user);
            
            //Set Access and Roles
            $userProjectAccess = new UserProjects();
            $userProjectAccess->setUser($user);
            $userProjectAccess->setRoles('Owner');
            $project->addUserProjects($userProjectAccess);
            
            $em->persist($project);
            $em->flush();

            return $this->redirect($this->generateUrl('project_show', array('id' => $project->getId())));
        }

        return array(
            'entity' => $project,
            'form'   => $form->createView(),
        );
    }
    
    /**
     * Creates a new Project entity.
     *
     * @Route("/{id}", name="project_commit")
     * @Method("POST")
     * @Template("VersionContolGitControlBundle:Project:show.html.twig")
     */
    public function commitAction(Request $request,$id)
    {
        $em = $this->getDoctrine()->getManager();

        $project= $em->getRepository('VersionContolGitControlBundle:Project')->find($id);

        if (!$project) {
            throw $this->createNotFoundException('Unable to find Project entity.');
        }
        
        //Checks Permission to project
        $this->checkProjectAuthorization($project,'EDIT');
        
        $comment = $this->get('request')->request->get('comment');
        if(!trim($comment)){
            throw $this->createNotFoundException('Please add comment');
        }
        
        $selectedFiles = $this->get('request')->request->get('files');
        if($selectedFiles && is_array($selectedFiles) && ($selectedFiles) > 0){

            $gitCommands = $this->get('version_control.git_command')->setProject($project);
            
            //Check if the 
            $gitStatusHash = $gitCommands->getStatusHash();
            $statusHash = $this->get('request')->request->get('statushash');
            if($gitStatusHash !== $statusHash){
                throw new \Exception('The git status has changed. Please refresh the page and retry the commit');
            }
            //$gitCommands = new GitCommands($gitPath);
            
            foreach($selectedFiles as $file){
                $gitCommands->stageFile($file);
            }
            
            $gitCommands->commit($comment);
            
            $this->get('session')->getFlashBag()->add('notice'
                , count($selectedFiles)." files have been committed");
        }else{
            //Error need to select at least on file
        }
        
        return $this->redirect($this->generateUrl('project_show', array('id' => $project->getId())));

    }
    
   

    /**
     * Creates a form to create a Project entity.
     *
     * @param Project $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(Project $entity)
    {
        $form = $this->createForm(new ProjectType(), $entity, array(
            'action' => $this->generateUrl('project_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new Project entity.
     *
     * @Route("/new", name="project_new")
     * @Method("GET")
     * @Template()
     */
    public function newAction()
    {
        $entity = new Project();
        $form   = $this->createCreateForm($entity);

        return array(
            'entity' => $entity,
            'form'   => $form->createView(),
        );
    }

    /**
     * Finds and displays a Project entity.
     *
     * @Route("/{id}", name="project_show")
     * @Method("GET")
     * @Template()
     */
    public function showAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $project= $em->getRepository('VersionContolGitControlBundle:Project')->find($id);

        if (!$project) {
            throw $this->createNotFoundException('Unable to find Project entity.');
        }
        
        $this->checkProjectAuthorization($project,'EDIT');
        
        //$gitPath = $project->getPath();
        //$gitCommands = $this->get('version_control.git_command')->setGitPath($gitPath);
        $gitCommands = $this->get('version_control.git_command')->setProject($project);
        
       $branchName = $gitCommands->getCurrentBranch();
       $files =  $gitCommands->getFilesToCommit();
       $statusHash = $gitCommands->getStatusHash();
       
        return array(
            'project'      => $project,
            'branchName' => $branchName,
            'files' => $files,
            'statusHash' => $statusHash
        );
    }

    /**
     * Finds and displays a Project entity.
     *
     * @Route("history/{id}", name="project_log")
     * @Method("GET")
     * @Template()
     */
    public function historyAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $project= $em->getRepository('VersionContolGitControlBundle:Project')->find($id);

        if (!$project) {
            throw $this->createNotFoundException('Unable to find Project entity.');
        }
        
        $this->checkProjectAuthorization($project,'VIEW');

        $gitCommands = $this->get('version_control.git_command')->setProject($project);
        $branchName = $gitCommands->getCurrentBranch();
        $gitLogs = $gitCommands->getLog(20,$branchName);

        
        
        return array(
            'project'      => $project,
            'branchName' => $branchName,
            'gitLogs' => $gitLogs,
        );
    }
    
    /**
     * Finds and displays a Project entity.
     *
     * @Route("push/{id}", name="project_push")
     * @Method("GET")
     * @Template()
     */
    public function pushAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $project= $em->getRepository('VersionContolGitControlBundle:Project')->find($id);

        if (!$project) {
            throw $this->createNotFoundException('Unable to find Project entity.');
        }
        
        $this->checkProjectAuthorization($project,'EDIT');

        $gitCommands = $this->get('version_control.git_command')->setProject($project);
        
        //Remote Server choice 
        $gitRemoteVersions = $gitCommands->getRemoteVersions();

        return array(
            'project'      => $project,
            'remoteVersions' => $gitRemoteVersions,
            'push_form' => $this->createPushPullForm($project,$gitCommands)->createView()
        );
    }
    
    /**
     * Finds and displays a Project entity.
     *
     * @Route("pushremote/{id}", name="project_pushremote")
     * @Method("POST")
     * @Template("VersionContolGitControlBundle:Project:push.html.twig")
     */
    public function pushToRemoteAction(Request $request,$id)
    {
        $em = $this->getDoctrine()->getManager();

        $project= $em->getRepository('VersionContolGitControlBundle:Project')->find($id);

        if (!$project) {
            throw $this->createNotFoundException('Unable to find Project entity.');
        }
        
        $this->checkProjectAuthorization($project,'EDIT');

        $gitCommands = $this->get('version_control.git_command')->setProject($project);
        $gitRemoteVersions = $gitCommands->getRemoteVersions();
        
        $pushForm = $this->createPushPullForm($project,$gitCommands);
        $pushForm->handleRequest($request);

        if ($pushForm->isValid()) {
            $data = $pushForm->getData();
            $remote = $data['remote'];
            $branch = $data['branch'];
            $response = $gitCommands->push($remote,$branch);
            
            $this->get('session')->getFlashBag()->add('notice', $response);
            
            return $this->redirect($this->generateUrl('project_push', array('id' => $id)));
        }

        return array(
            'project'      => $project,
            'remoteVersions' => $gitRemoteVersions,
            'push_form' => $this->createPushPullForm($project,$gitCommands)->createView()
        );
    }
    
    /**
    * Creates a form to edit a Project entity.
    *
    * @param Project $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createPushPullForm($project,$gitCommands,$formAction = 'project_pushremote')
    {
                //Remote Server choice 
        $gitRemoteVersions = $gitCommands->getRemoteVersions();
        $remoteChoices = array();
        foreach($gitRemoteVersions as $remoteVersion){
            $remoteChoices[$remoteVersion[0]] = $remoteVersion[0].'('.$remoteVersion[1].')'; 
        }
        
        //Local Branch choice
        $branches = $gitCommands->getLocalBranches();
        $branchChoices = array();
        foreach($branches as $branchName){
            $branchChoices[$branchName] = $branchName;
        }
               
        //Current branch
        $currentBranch = $gitCommands->getCurrentBranch();
        
        reset($remoteChoices);
        $firstOrigin = key($remoteChoices);
        
        $defaultData = array('branch' => $currentBranch);
        $form = $this->createFormBuilder($defaultData, array(
                'action' => $this->generateUrl($formAction, array('id' => $project->getId())),
                'method' => 'POST',
            ))
            ->add('remote', 'choice', array(
                'label' => 'Remote Server'
                ,'choices'  => $remoteChoices
                ,'data' => $firstOrigin
                ,'required' => false
                ,'constraints' => array(
                    new NotBlank()
                ))
            )   
            ->add('branch', 'choice', array(
                'label' => 'Branch'
                ,'choices'  => $branchChoices
                ,'preferred_choices' => array($currentBranch)
                ,'data' => trim($currentBranch)
                ,'required' => false
                ,'constraints' => array(
                    new NotBlank()
                ))
            )   
            ->getForm();

        $form->add('submit', 'submit', array('label' => 'Push'));
        return $form;
    }
    

    
    /**
     * Form to choose which brabch and remote a user will pull.
     * This is just the form. Also see pullToLocal() 
     *
     * @Route("pull/{id}", name="project_pull")
     * @Method("GET")
     * @Template()
     */
    public function pullAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $project= $em->getRepository('VersionContolGitControlBundle:Project')->find($id);

        if (!$project) {
            throw $this->createNotFoundException('Unable to find Project entity.');
        }
        $this->checkProjectAuthorization($project,'EDIT');

        $gitCommands = $this->get('version_control.git_command')->setProject($project);
        
        //Remote Server choice 
        $gitRemoteVersions = $gitCommands->getRemoteVersions();

        $pullForm = $this->createPushPullForm($project,$gitCommands,"project_pulllocal");
        $pullForm->add('submit', 'submit', array('label' => 'Pull'));
        
        return array(
            'project'      => $project,
            'remoteVersions' => $gitRemoteVersions,
            'pull_form' => $pullForm->createView()
        );
    }
    
    /**
     * Pulls git repository from remote to local.
     *
     * @Route("pulllocal/{id}", name="project_pulllocal")
     * @Method("POST")
     * @Template("VersionContolGitControlBundle:Project:push.html.twig")
     */
    public function pullToLocalAction(Request $request,$id)
    {
        $em = $this->getDoctrine()->getManager();

        $project= $em->getRepository('VersionContolGitControlBundle:Project')->find($id);

        if (!$project) {
            throw $this->createNotFoundException('Unable to find Project entity.');
        }
        $this->checkProjectAuthorization($project,'EDIT');

        $gitCommands = $this->get('version_control.git_command')->setProject($project);
        $gitRemoteVersions = $gitCommands->getRemoteVersions();
        
        $pullForm = $this->createPushPullForm($project,$gitCommands,"project_pulllocal");
        $pullForm->add('submit', 'submit', array('label' => 'Pull'));
        $pullForm->handleRequest($request);

        if ($pullForm->isValid()) {
            $data = $pullForm->getData();
            $remote = $data['remote'];
            $branch = $data['branch'];
            $response = $gitCommands->pull($remote,$branch);
            
            $this->get('session')->getFlashBag()->add('notice', $response);
            
            return $this->redirect($this->generateUrl('project_pull', array('id' => $id)));
        }

        return array(
            'project'      => $project,
            'remoteVersions' => $gitRemoteVersions,
            'pull_form' => $pullForm->createView()
        );
    }
    
    /**
     * Displays a form to edit an existing Project entity.
     *
     * @Route("/{id}/edit", name="project_edit")
     * @Method("GET")
     * @Template()
     */
    public function editAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $project = $em->getRepository('VersionContolGitControlBundle:Project')->find($id);

        if (!$project) {
            throw $this->createNotFoundException('Unable to find Project entity.');
        }
        
        $this->checkProjectAuthorization($project,'MASTER');

        $editForm = $this->createEditForm($project);
        $deleteForm = $this->createDeleteForm($id);

        return array(
            'project'      => $project,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }

    /**
    * Creates a form to edit a Project entity.
    *
    * @param Project $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(Project $entity)
    {
        $form = $this->createForm(new ProjectType(), $entity, array(
            'action' => $this->generateUrl('project_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing Project entity.
     *
     * @Route("/{id}", name="project_update")
     * @Method("PUT")
     * @Template("VersionContolGitControlBundle:Project:edit.html.twig")
     */
    public function updateAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $project = $em->getRepository('VersionContolGitControlBundle:Project')->find($id);

        if (!$project) {
            throw $this->createNotFoundException('Unable to find Project entity.');
        }
        
        $this->checkProjectAuthorization($project,'MASTER');

        $deleteForm = $this->createDeleteForm($id);
        $editForm = $this->createEditForm($project);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $em->flush();

            return $this->redirect($this->generateUrl('project_edit', array('id' => $id)));
        }

        return array(
            'entity'      => $project,
            'edit_form'   => $editForm->createView(),
            'delete_form' => $deleteForm->createView(),
        );
    }
    /**
     * Deletes a Project entity.
     *
     * @Route("/{id}", name="project_delete")
     * @Method("DELETE")
     */
    public function deleteAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $project = $em->getRepository('VersionContolGitControlBundle:Project')->find($id);

        if (!$project) {
            throw $this->createNotFoundException('Unable to find Project entity.');
        }
        
        $this->checkProjectAuthorization($project,'MASTER');
            
        $form = $this->createDeleteForm($id);
        $form->handleRequest($request);
        
        
        if ($form->isValid()) {
            $em->remove($project);
            $em->flush();
        }

        return $this->redirect($this->generateUrl('project'));
    }

    /**
     * Creates a form to delete a Project entity by id.
     *
     * @param mixed $id The entity id
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createDeleteForm($id)
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('project_delete', array('id' => $id)))
            ->setMethod('DELETE')
            ->add('submit', 'submit', array('label' => 'Delete'))
            ->getForm()
        ;
    }
    
     /**
     * Form to choose which brabch and remote a user will pull.
     * This is just the form. Also see pullToLocal() 
     *
     * @Route("branches/{id}", name="project_branches")
     * @Method("GET")
     * @Template()
     */
    public function branchesAction($id)
    {
        $em = $this->getDoctrine()->getManager();

        $project= $em->getRepository('VersionContolGitControlBundle:Project')->find($id);

        if (!$project) {
            throw $this->createNotFoundException('Unable to find Project entity.');
        }
        
        $this->checkProjectAuthorization($project,'VIEW');

        $gitCommands = $this->get('version_control.git_command')->setProject($project);
        
        $branchName = $gitCommands->getCurrentBranch();
        //Remote Server choice 
        $gitLocalBranches = $gitCommands->getLocalBranches();
        
        $gitLogs = $gitCommands->getLog(1,$branchName);

        $form = $this->createNewBranchForm($project);
        
        return array(
            'project'      => $project,
            'branches' => $gitLocalBranches,
            'branchName' => $branchName,
            'form' => $form->createView(),
            'gitLogs' => $gitLogs
        );
    }
    
    /**
    * Creates a form to edit a Project entity.
    *
    * @param Project $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createNewBranchForm($project,$formAction = 'project_branch')
    {

        $defaultData = array();
        $form = $this->createFormBuilder($defaultData, array(
                'action' => $this->generateUrl($formAction, array('id' => $project->getId())),
                'method' => 'POST',
            ))
            ->add('name', 'text', array(
                'label' => 'Branch Name'
                ,'required' => true
                ,'constraints' => array(
                    new NotBlank()
                ))
            )   
            ->add('switch', 'checkbox', array(
                'label' => 'Switch to branch on creation'
                ,'required' => false
                )
            )   
            ->getForm();

        $form->add('submit', 'submit', array('label' => 'Create'));
        return $form;
    }
    
    /**
     * Pulls git repository from remote to local.
     *
     * @Route("branch/{id}", name="project_branch")
     * @Method("POST")
     * @Template("VersionContolGitControlBundle:Project:branches.html.twig")
     */
    public function branchAction(Request $request,$id)
    {
        $em = $this->getDoctrine()->getManager();

        $project= $em->getRepository('VersionContolGitControlBundle:Project')->find($id);

        if (!$project) {
            throw $this->createNotFoundException('Unable to find Project entity.');
        }
        
        $this->checkProjectAuthorization($project,'OPERATOR');

        $gitCommands = $this->get('version_control.git_command')->setProject($project);
        
        $branchName = $gitCommands->getCurrentBranch();
        $gitLocalBranches = $gitCommands->getLocalBranches();
        $gitLogs = $gitCommands->getLog(1,$branchName);
        
        $form = $this->createNewBranchForm($project);
        
        $form->handleRequest($request);

        if ($form->isValid()) {
            $data = $form->getData();
            $newBranchName = $data['name'];
            $switchToBranch= $data['switch'];
            try{
                
                $response = $gitCommands->createLocalBranch($newBranchName,$switchToBranch);
                $this->get('session')->getFlashBag()->add('notice', $response);
                return $this->redirect($this->generateUrl('project_branches', array('id' => $id)));
                
            }catch(\Exception $e){
               $this->get('session')->getFlashBag()->add('error', $e->getMessage()); 
            }
            
            
        }

        return array(
           'project'      => $project,
            'branches' => $gitLocalBranches,
            'branchName' => $branchName,
            'form' => $form->createView(),
            'gitLogs' => $gitLogs
        );
    }
    
    /**
     * Pulls git repository from remote to local.
     *
     * @Route("checkoutbranch/{id}/{branchName}", name="project_checkoutbranch")
     * @Method("GET")
     * @Template("VersionContolGitControlBundle:Project:branches.html.twig")
     */
    public function checkoutBranchAction($id, $branchName){
        $em = $this->getDoctrine()->getManager();

        $project= $em->getRepository('VersionContolGitControlBundle:Project')->find($id);

        if (!$project) {
            throw $this->createNotFoundException('Unable to find Project entity.');
        }
        
        $this->checkProjectAuthorization($project,'OPERATOR');

        $gitCommands = $this->get('version_control.git_command')->setProject($project);
        
        $response = $gitCommands->checkoutBranch($branchName);
        
        $this->get('session')->getFlashBag()->add('notice', $response);
        
        return $this->redirect($this->generateUrl('project_branches', array('id' => $id)));
    }
    
    /**
     * Show Git commit diff
     *
     * @Route("commitdiff/{id}/{commitHash}", name="project_commitdiff")
     * @Method("GET")
     * @Template()
     */
    public function commitDiffAction($id,$commitHash){
        $em = $this->getDoctrine()->getManager();

        $project= $em->getRepository('VersionContolGitControlBundle:Project')->find($id);

        if (!$project) {
            throw $this->createNotFoundException('Unable to find Project entity.');
        }
        
        $this->checkProjectAuthorization($project,'VIEW');
        
        $gitCommands = $this->get('version_control.git_command')->setProject($project);
        
        $branchName = $gitCommands->getCurrentBranch();
        $gitLog = $gitCommands->getCommitLog($commitHash,$branchName);
        
        $gitDiffs = $gitCommands->getCommitDiff($commitHash);
        
        return array(
            'project'      => $project,
            'branchName' => $branchName,
            'log' => $gitLog,
            'diffs' => $gitDiffs,
        );
    }
    
    /**
     * Show Git commit diff
     *
     * @Route("filediff/{id}/{difffile}", name="project_filediff")
     * @Method("GET")
     * @Template()
     */
    public function fileDiffAction($id,$difffile){
        $em = $this->getDoctrine()->getManager();

        $difffile = urldecode($difffile);
       
        $project= $em->getRepository('VersionContolGitControlBundle:Project')->find($id);

        if (!$project) {
            throw $this->createNotFoundException('Unable to find Project entity.');
        }
        
        $this->checkProjectAuthorization($project,'VIEW');
        
        $gitCommands = $this->get('version_control.git_command')->setProject($project);
        
        $branchName = $gitCommands->getCurrentBranch();
       // $gitLog = $gitCommands->getCommitLog($commitHash,$branchName);
        
        $gitDiffs = $gitCommands->getDiffFile($difffile);
   
        return array(
            'project'      => $project,
            'branchName' => $branchName,
            'diffs' => $gitDiffs,
        );
    }
    
    /**
     * Show Git commit diff
     *
     * @Route("/filediff/{id}/{currentDir}",defaults={"$currentDir" = ""}, name="project_filelist")
     * @Method("GET")
     * @Template()
     */
    public function fileListAction($id,$currentDir = ''){
        $em = $this->getDoctrine()->getManager();

       
        $project= $em->getRepository('VersionContolGitControlBundle:Project')->find($id);

        if (!$project) {
            throw $this->createNotFoundException('Unable to find Project entity.');
        }
        
        $this->checkProjectAuthorization($project,'VIEW');
        
        $gitCommands = $this->get('version_control.git_command')->setProject($project);
        
        $branchName = $gitCommands->getCurrentBranch();
        $dir = $project->getPath();
        if($currentDir){
            $dir .= trim(urldecode($currentDir));
        }
        $files = $gitCommands->listFiles($dir, $branchName);
       
   
        return array(
            'project'      => $project,
            'branchName' => $branchName,
            'files' => $files,
            'currentDir' => $currentDir
        );
    }
    
    
    /**
     * 
     * @param VersionContol\GitControlBundle\Entity\Project $project
     * @throws AccessDeniedException
     */
    protected function checkProjectAuthorization(\VersionContol\GitControlBundle\Entity\Project $project,$grantType='MASTER'){
        $authorizationChecker = $this->get('security.authorization_checker');

        // check for edit access
        if (false === $authorizationChecker->isGranted($grantType, $project)) {
            throw new AccessDeniedException();
        }
    }
}
