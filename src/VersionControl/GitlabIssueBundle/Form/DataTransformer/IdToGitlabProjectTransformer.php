<?php

// src/AppBundle/Form/DataTransformer/IssueToNumberTransformer.php
namespace VersionControl\GitlabIssueBundle\Form\DataTransformer;


use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Form\Exception\TransformationFailedException;
use VersionControl\GitlabIssueBundle\Entity\GitlabProject;


class IdToGitlabProjectTransformer implements DataTransformerInterface
{
    private $manager;

    public function __construct()
    {
        //$this->manager = $manager;
    }

    /**
     * Transforms an id to gitLabProject object .
     *
     * @param  Issue|null $id
     * @return string
     */
    public function transform($id)
    {
        if (null === $id) {
            return null;
        }
         
        $gitlabProject = new GitlabProject();
        $gitlabProject->setId($id);
        //$gitLabProjectEntity->setName($gitLabProject['name']);
        

        return $gitlabProject;
    }

    /**
     * Transforms a string (number) to an object (issue).
     *
     * @param  string $issueNumber
     * @return Issue|null
     * @throws TransformationFailedException if object (issue) is not found.
     */
    public function reverseTransform($gitlabProject)
    {
        if($gitlabProject){
            return (string)$gitlabProject->getId();
        }
        
        return;
    }
}

