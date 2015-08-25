<?php

namespace VersionContol\GitControlBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use VersionContol\GitControlBundle\Utility\GitCommands;

class DefaultController extends Controller
{


    /**
     * @Route("/", name="home")
     * @Template()
     */
    public function listAction()
    {
        $em = $this->getDoctrine()->getManager();

        //$projects = $em->getRepository('VersionContolGitControlBundle:Project')->findAll();
        $user = $this->get('security.token_storage')->getToken()->getUser();
        
        $userProjects = $em->getRepository('VersionContolGitControlBundle:UserProjects')->findByUser($user);

        return array(
            'userProjects' => $userProjects,
            'user' => $user,
        );
    }
    
}
