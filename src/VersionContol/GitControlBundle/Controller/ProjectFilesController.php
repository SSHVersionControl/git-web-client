<?php

namespace VersionContol\GitControlBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use VersionContol\GitControlBundle\Controller\Base\BaseProjectController;
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
 * @Route("/project/file")
 */
class ProjectFilesController extends BaseProjectController{
    
    /**
     *
     * @var GitCommand 
     */
    protected $gitFilesCommands;
    
    
    /**
     * Show Git commit diff
     *
     * @Route("s/{id}/{currentDir}",defaults={"$currentDir" = ""}, name="project_filelist")
     * @Method("GET")
     * @Template()
     */
    public function fileListAction($id,$currentDir = ''){
        
        $this->initAction($id);

        $dir = '';
        if($currentDir){
            $dir = trim(urldecode($currentDir));
        }
        $files = $this->gitFilesCommands->listFiles($dir, $this->branchName);
        
        $readme = '';
        foreach($files as $file){
            if(strtolower($file->getExtension()) == 'md' || strtolower($file->getExtension()) == 'markdown'){
                $readme = $this->gitFilesCommands->readFile($file);
                break;
            }
        }
   
        return array_merge($this->viewVariables, array(
            'files' => $files,
            'currentDir' => $dir,
            'readme' => $readme
        ));
    }
    
    
    /**
     * 
     * @param integer $id Project Id
     */
    protected function initAction($id,$grantType = 'VIEW'){
 
        $em = $this->getDoctrine()->getManager();

        $this->project= $em->getRepository('VersionContolGitControlBundle:Project')->find($id);

        if (!$this->project) {
            throw $this->createNotFoundException('Unable to find Project entity.');
        }
        $this->checkProjectAuthorization($this->project,$grantType);
        
        $this->gitFilesCommands = $this->get('version_control.git_files')->setProject($this->project);
        
        $this->branchName = $this->gitFilesCommands->getCurrentBranch();
        
        $this->viewVariables = array_merge($this->viewVariables, array(
            'project'      => $this->project,
            'branchName' => $this->branchName,
            ));
    }
    
    /**
     * Adds File to .gitignore and remove file git index.
     *
     * @Route("/{id}/ignore/{filePath}", name="project_fileignore")
     * @Method("GET")
     * @Template("VersionContolGitControlBundle:ProjectFiles:fileList.html.twig")
     */
    public function ignoreAction($id,$filePath){
        $this->initAction($id,'MASTER');
        
        $filePath = trim(urldecode($filePath));
        
        $response = $this->gitFilesCommands->ignoreFile($filePath, $this->branchName);
        
        $this->get('session')->getFlashBag()->add('notice', $response);
            
        return $this->redirect($this->generateUrl('project_filelist', array('id' => $id)));
    }
    
   
}

