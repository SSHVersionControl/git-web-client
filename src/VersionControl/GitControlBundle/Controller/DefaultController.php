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

        //$projects = $em->getRepository('VersionControlGitControlBundle:Project')->findAll();
        $user = $this->get('security.token_storage')->getToken()->getUser();
        
        //$userProjects = $em->getRepository('VersionControlGitControlBundle:UserProjects')->findByUser($user);
        
        $keyword = $request->query->get('keyword', false);
        
        $query = $em->getRepository('VersionControlGitControlBundle:UserProjects')->findByUserAndKeyword($user,$keyword,true)->getQuery();
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
