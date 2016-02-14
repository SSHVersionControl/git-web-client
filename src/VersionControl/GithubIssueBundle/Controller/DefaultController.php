<?php

namespace VersionControl\GithubIssueBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('VersionControlGithubIssueBundle:Default:index.html.twig', array('name' => $name));
    }
}
