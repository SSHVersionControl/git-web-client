<?php

namespace VersionContol\GitControlBundle\Controller\Base;

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
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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
        $mergedParameters = array_merge(array('id'=>$this->project->getId()),$parameters);
        parent::generateUrl($route, $mergedParameters,$referenceType);
    }
    
}

