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
    
}

