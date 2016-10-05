<?php
/*
 * This file is part of the GitControlBundle package.
 *
 * (c) Paul Schweppe <paulschweppe@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace VersionControl\GitControlBundle\Controller\Base;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use VersionControl\GitControlBundle\Entity\Project;
use VersionControl\GitControlBundle\Form\ProjectType;
use Symfony\Component\Validator\Constraints\NotBlank;
use VersionControl\GitControlBundle\Entity\UserProjects;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Base Project controller.
 * @abstract
 */
abstract class BaseProjectController extends Controller{
    
    /**
     * The current Project Entity
     * @var Project 
     */
    protected $project;
    
    /**
     * Array of variables the will be past to the twig templating engine
     * @var array 
     */
    protected $viewVariables = array();
    
    /**
     * The current branch name
     * @var string 
     */
    protected $branchName;
    
    /**
     *
     * @var GitCommand 
     */
    protected $gitCommands;
    
    protected $projectGrantType = 'VIEW';
    
    /**
     * Allow access by ajax only request
     * @var boolean 
     */
    protected $ajaxOnly = true;
    
    /**
     * 
     * @param VersionControl\GitControlBundle\Entity\Project $project
     * @throws AccessDeniedException
     */
    protected function checkProjectAuthorization(\VersionControl\GitControlBundle\Entity\Project $project,$grantType='MASTER'){
        $authorizationChecker = $this->get('security.authorization_checker');

        // check for edit access
        if (false === $authorizationChecker->isGranted($grantType, $project)) {
            throw new AccessDeniedException();
        }
    }
    
    /**
     * Generates a URL from the given parameters adding project id.
     *
     * @param string $route         The name of the route
     * @param mixed  $parameters    An array of parameters
     * @param int    $referenceType The type of reference (one of the constants in UrlGeneratorInterface)
     *
     * @return string The generated URL
     *
     * @see UrlGeneratorInterface
     */
    public function generateUrl($route, $parameters = array(),$referenceType = UrlGeneratorInterface::ABSOLUTE_PATH) {
        if($this->project){
            $mergedParameters = array_merge(array('id'=>$this->project->getId()),$parameters);
        }
        return parent::generateUrl($route, $mergedParameters,$referenceType);
    }
    
    /**
     * 
     * @param integer $id
     */
    public function initAction($id,$grantType = 'VIEW'){
 
        $em = $this->getDoctrine()->getManager();

        $this->project= $em->getRepository('VersionControlGitControlBundle:Project')->find($id);

        if (!$this->project) {
            throw $this->createNotFoundException('Unable to find Project entity.');
        }
        
        //Redirect is not ajax
        $request  = $this->container->get('request_stack')->getCurrentRequest();
        //$request  = $this->getRequest();
        if( $this->ajaxOnly == true && !$request->isXmlHttpRequest()){

             //print_r($request->getRequestUri());
             return $this->generateUrl('project',array('section'=> urlencode($request->getRequestUri())));
        }
        
        $this->checkProjectAuthorization($this->project,$grantType);
        
        $projectEnvironment = $this->getProjectEnvironment();
        
        $this->gitCommands = $this->get('version_control.git_commands')->setGitEnvironment($projectEnvironment);
        
        $this->branchName = $this->gitCommands->command('branch')->getCurrentBranch();
        
        $this->viewVariables = array_merge($this->viewVariables, array(
            'project'      => $this->project,
            'branchName' => $this->branchName,
            ));
        
       
       
    }
    
    /**
     * Sets the project entity
     * @param Project $project
     */
    public function getProjectEnvironment() {
        $projectEnvironmentStorage = $this->get('version_control.project_environmnent_storage');
        return $projectEnvironmentStorage->getProjectEnviromment($this->project);
    }
    
    /**
     * Sets the container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     */
    /*public function setContainer(ContainerInterface $container = null){
        $this->container = $container;
        $request = $this->container->get('request_stack')->getCurrentRequest();
        $id = $request->get('id', false);
        if($id){
            $grantType = $this->getGrantType();
            print_r($grantType);
            $this->initAction($id,$grantType);
        }
    }*/
    
    public function getGrantType(){
        return $this->projectGrantType;
    }
    
    public function getProjectGrantType() {
        return $this->projectGrantType;
    }

    public function setProjectGrantType($projectGrantType) {
        $this->projectGrantType = $projectGrantType;
        return $this;
    }


    
}

