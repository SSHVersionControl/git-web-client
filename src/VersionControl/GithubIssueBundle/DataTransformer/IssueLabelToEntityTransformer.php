<?php
/*
 * This file is part of the GithubIssueBundle package.
 *
 * (c) Paul Schweppe <paulschweppe@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace VersionControl\GithubIssueBundle\DataTransformer;

use VersionControl\GithubIssueBundle\DataTransformer\DataTransformerInterface;
use VersionControl\GithubIssueBundle\Entity\Issues\IssueLabel;
use Symfony\Component\Form\Exception\TransformationFailedException;

class IssueLabelToEntityTransformer implements DataTransformerInterface
{

    /**
     * Transforms an issue array into an issue Entity object.
     *
     * @param  \VersionControl\GithubIssueBundle\Entity\Issues\IssueLabel|null $issueLabel
     * @return string
     */
    public function transform($issueLabel)
    {
        if (null === $issueLabel) {
            return null;
        }
        
        $issueLabelEntity = new IssueLabel();
        $issueLabelEntity->setId($issueLabel['name']);
        $issueLabelEntity->setTitle($issueLabel['name']);
        $issueLabelEntity->setHexColor($issueLabel['color']);

        return $issueLabelEntity;
    }

    /**
     * Transforms a string (number) to an object (issue).
     *
     * @param  VersionControl\GithubIssueBundle\Entity\Issues\IssueLabel $issueLabel
     * @return string|null label name
     * @throws TransformationFailedException if object (issue) is not found.
     */
    public function reverseTransform($issueLabelEntity)
    {
        if ($issueLabelEntity === null) {
            // causes a validation error
            throw new TransformationFailedException('issueLabelEntity is null');
        }
        
        return $issueLabelEntity->getId();
    }
}