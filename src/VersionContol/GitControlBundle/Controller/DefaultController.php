<?php

namespace VersionContol\GitControlBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Symfony\Component\HttpFoundation\Request;

/**
 * 
 */
class DefaultController extends Controller
{


    /**
     * @Route("/", name="home")
     * @Template()
     */
    public function listAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        //$projects = $em->getRepository('VersionContolGitControlBundle:Project')->findAll();
        $user = $this->get('security.token_storage')->getToken()->getUser();
        
        //$userProjects = $em->getRepository('VersionContolGitControlBundle:UserProjects')->findByUser($user);
        
        $keyword = $request->query->get('keyword', false);
        
        $query = $em->getRepository('VersionContolGitControlBundle:UserProjects')->findByUserAndKeyword($user,$keyword,true)->getQuery();
        $paginator  = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1)/*page number*/,
            15/*limit per page*/
        );

        return array(
            'userProjects' => $pagination,
            'user' => $user,
        );
    }
    
}
