<?php
/*
 * This file is part of the GitlabIssueBundle package.
 *
 * (c) Paul Schweppe <paulschweppe@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace VersionControl\GitlabIssueBundle\DataTransformer;

use VersionControl\GitlabIssueBundle\DataTransformer\DataTransformerInterface;
use VersionControl\GitlabIssueBundle\Entity\GitlabProject;
use Symfony\Component\Form\Exception\TransformationFailedException;

class GitlabProjectToEntityTransformer implements DataTransformerInterface
{
    

    /**
     * Transforms an issue array into an issue Entity object.
     *
     * @param  array $gitLabProject
     * @return \VersionControl\GitlabIssueBundle\Entity\GitlabProject|null
     */
    public function transform($gitLabProject)
    {
        if (null === $gitLabProject) {
            return null;
        }
        
        $gitLabProjectEntity = new GitlabProject();
        $gitLabProjectEntity->setId($gitLabProject['id']);
        $gitLabProjectEntity->setName($gitLabProject['name']);

        return $gitLabProjectEntity;
    }

    /**
     * Transforms a string (number) to an object (issue).
     *
     * @param  VersionControl\GitlabIssueBundle\Entity\Issues\GitlabProject $gitLabProject
     * @return string|null label name
     * @throws TransformationFailedException if object (issue) is not found.
     */
    public function reverseTransform($gitLabProjectEntity)
    {
        if ($gitLabProjectEntity === null) {
            // causes a validation error
            throw new TransformationFailedException('userEntity is null');
        }
        
        return $gitLabProjectEntity->getName();
    }
}