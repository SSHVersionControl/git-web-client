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
use VersionControl\GithubIssueBundle\Entity\Issues\IssueMilestone;
use Symfony\Component\Form\Exception\TransformationFailedException;

class IssueMilestoneToEntityTransformer implements DataTransformerInterface
{

    private $userTransformer;

    public function __construct()
    {
        $this->userTransformer = new UserToEntityTransformer();
    }

    /**
     * Transforms an issueMilestone array into an issueMilestone Entity object.
     *
     * @param  IssueMilestone|null $issueMilestone
     * @return string
     */
    public function transform($issueMilestone)
    {
        if (null === $issueMilestone) {
            return null;
        }
        
        $issueMilestoneEntity = new IssueMilestone();
        $issueMilestoneEntity->setId($issueMilestone['number']);
        $issueMilestoneEntity->setTitle($issueMilestone['title']);
        $issueMilestoneEntity->setState($issueMilestone['state']);
        $issueMilestoneEntity->setDescription($issueMilestone['description']);
        $issueMilestoneEntity->setCreatedAt($this->formatDate($issueMilestone['created_at']));
        $issueMilestoneEntity->setClosedAt($this->formatDate($issueMilestone['closed_at']));
        $issueMilestoneEntity->setUpdatedAt($this->formatDate($issueMilestone['updated_at']));
        $issueMilestoneEntity->setDueOn($this->formatDate($issueMilestone['due_on']));
        
        // Possible Fields
        // "open_issues": 4,
        // "closed_issues": 8,

        //Set User
        if(isset($issueMilestone['creator']) && is_array($issueMilestone['creator'])){
            $user = $this->userTransformer->transform($issueMilestone['creator']);
            $issueMilestoneEntity->setUser($user);
        }

        return $issueMilestoneEntity;
    }

    /**
     * Transforms an issueMilestone entity into a git api captiable issueMilestone array.
     *
     * @param  \VersionControl\GithubIssueMilestoneBundle\Entity\IssueMilestones $issueMilestoneEntity
     * @return array|null
     * @throws TransformationFailedException if object (issueMilestone) is not found.
     */
    public function reverseTransform($issueMilestoneEntity)
    {
        if ($issueMilestoneEntity === null) {
            // causes a validation error
            throw new TransformationFailedException('IssueMilestoneEntity is null');
        }
        
        $issueMilestone = array(
            'title' =>  $issueMilestoneEntity->getTitle()
            ,'description' =>  $issueMilestoneEntity->getDescription()
            ,'state' =>  $issueMilestoneEntity->getStatus()
            //,'milestone' =>  0
        );

        return $issueMilestone;
    }
    
    protected function formatDate($date){
        try {
            $dateTime = new \DateTime($date);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
        return $dateTime;
    }
}