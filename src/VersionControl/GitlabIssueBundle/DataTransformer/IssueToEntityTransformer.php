<?php
namespace VersionControl\GitlabIssueBundle\DataTransformer;

use VersionControl\GitlabIssueBundle\DataTransformer\DataTransformerInterface;
use VersionControl\GitlabIssueBundle\Entity\Issues\Issue;
use Symfony\Component\Form\Exception\TransformationFailedException;

class IssueToEntityTransformer implements DataTransformerInterface
{
    private $issueLabelTransformer;
    
    private $issueMilestoneTransformer;
    
    private $issueCommentTransformer;
    
    private $userTransformer;

    public function __construct()
    {
        $this->issueLabelTransformer = new IssueLabelToEntityTransformer();
        $this->issueMilestoneTransformer = new IssueMilestoneToEntityTransformer();
        $this->issueCommentTransformer = new IssueCommentToEntityTransformer();
        $this->userTransformer = new UserToEntityTransformer();
    }

    /**
     * Transforms an issue array into an issue Entity object.
     *
     * @param  Issue|null $issue
     * @return string
     */
    public function transform($issue)
    {
        if (null === $issue) {
            return null;
        }
        
        $issueEntity = new Issue();
        $issueEntity->setId($issue['id']);
        $issueEntity->setTitle($issue['title']);
        $issueEntity->setStatus($this->formatState($issue['state']));
        $issueEntity->setDescription($issue['description']);
        $issueEntity->setCreatedAt($this->formatDate($issue['created_at']));
        if(isset($issue['closed_at'])){
            $issueEntity->setClosedAt($this->formatDate($issue['closed_at']));
        }
        $issueEntity->setUpdatedAt($this->formatDate($issue['updated_at']));
        
        //Map Issue labels
        if(isset($issue['labels']) && is_array($issue['labels'])){
            foreach($issue['labels'] as $label){
                $issueLabelEntity = $this->issueLabelTransformer->transform($label);
                $issueEntity->addIssueLabel($issueLabelEntity);
            }
        }
        
        if(isset($issue['author']) && is_array($issue['author'])){
            $user = $this->userTransformer->transform($issue['author']);
            $issueEntity->setUser($user);
        }
        if(isset($issue['milestone']) && is_array($issue['milestone'])){
            $issueMilestoneEntity = $this->issueMilestoneTransformer->transform($issue['milestone']);
            $issueEntity->setIssueMilestone($issueMilestoneEntity);
        }

        return $issueEntity;
    }

    /**
     * Transforms an issue entity into a git api captiable issue array.
     *
     * @param  \VersionControl\GitlabIssueBundle\Entity\Issues $issueEntity
     * @return array|null
     * @throws TransformationFailedException if object (issue) is not found.
     */
    public function reverseTransform($issueEntity)
    {
        if ($issueEntity === null) {
            // causes a validation error
            throw new TransformationFailedException('IssueEntity is null');
        }
        
        $issue = array(
            'title' =>  $issueEntity->getTitle()
            ,'description' =>  $issueEntity->getDescription()
            //,'state' =>  $issueEntity->getStatus()
            //,'milestone' =>  0
        );
        if($issueEntity->getIssueMilestone()){
           $issue['milestone_id'] =  $issueEntity->getIssueMilestone()->getId();
        }
        $labels = array();
        foreach($issueEntity->getIssueLabel() as $issueLabel){
            $labels[] = $issueLabel->getId();
        }
        $issue['labels'] = implode(',',$labels);

        return $issue;
    }
    
    protected function formatDate($date){
        try {
            $dateTime = new \DateTime($date);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
        return $dateTime;
    }
    
    protected function formatState($state){
        if($state === 'opened' || $state === 'activated'){
            return 'open';
        }
        
        return $state;
    }
}