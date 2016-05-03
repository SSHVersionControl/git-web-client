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
use VersionControl\GitlabIssueBundle\Entity\Issues\IssueLabel;
use Symfony\Component\Form\Exception\TransformationFailedException;

class IssueLabelToEntityTransformer implements DataTransformerInterface
{

    /**
     * Transforms an issue array into an issue Entity object.
     *
     * @param  \VersionControl\GitlabIssueBundle\Entity\Issues\IssueLabel|null $issueLabel
     * @return string
     */
    public function transform($issueLabel)
    {
        if (null === $issueLabel) {
            return null;
        }
        
        $issueLabelEntity = new IssueLabel();
        
        if(is_array($issueLabel)){
            $issueLabelEntity->setId($issueLabel['name']);
            $issueLabelEntity->setTitle($issueLabel['name']);
            $issueLabelEntity->setHexColor($this->stripHash($issueLabel['color']));
        }elseif(is_string($issueLabel) || is_numeric($issueLabel)){
            $issueLabelEntity->setId($issueLabel);
            $issueLabelEntity->setTitle($issueLabel);
            $issueLabelEntity->setHexColor('ffffff');
        }
        
        
        

        return $issueLabelEntity;
    }

    /**
     * Transforms a string (number) to an object (issue).
     *
     * @param  VersionControl\GitlabIssueBundle\Entity\Issues\IssueLabel $issueLabel
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
    
    /**
     * Removes # from string
     * 
     * @param string $colorWithHash
     * @return Color hex
     */
    public function stripHash($colorWithHash){
        return str_replace('#','',$colorWithHash);
    }
}