<?php
// src/VersionControl/GitlabIssueBundle/Form/DataTransformer/IdToGitlabProjectTransformer.php
/*
 * This file is part of the GitlabIssueBundle package.
 *
 * (c) Paul Schweppe <paulschweppe@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
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

