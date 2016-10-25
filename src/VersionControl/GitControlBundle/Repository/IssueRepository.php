<?php
/*
 * This file is part of the GitControlBundle package.
 *
 * (c) Paul Schweppe <paulschweppe@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace VersionControl\GitControlBundle\Repository;

use Doctrine\ORM\EntityRepository;
use VersionControl\GitControlBundle\Repository\Issues\IssueRepositoryInterface;
use VersionControl\GitControlBundle\Entity\Issue;
use VersionControl\GitControlBundle\Entity\IssueComment;

class IssueRepository extends EntityRepository implements IssueRepositoryInterface
{
    protected $project;

    protected $currentUser;

    /**
     * Finds issues for a state.
     *
     * @param string $keyword
     *
     * @return array of issues
     */
    public function findIssues($keyword, $state = 'open')
    {
        return $this->findByProjectAndStatus($state, $keyword, null, true);
    }

    /**
     * Finds issues for a state.
     *
     * @param string $keyword
     *
     * @return array of issues
     */
    public function countFindIssues($keyword, $state = 'open')
    {
        return $this->countIssuesForProjectWithStatus($state, $keyword);
    }

    /**
     * @param int $id
     */
    public function findIssueById($id)
    {
        $issueEntity = $this->find($id);

        if (!$issueEntity) {
            throw $this->createNotFoundException('Unable to find Issue entity.');
        }

        return $issueEntity;
    }

    /**
     * @param type $issue
     */
    public function newIssue()
    {
        $issueEntity = new Issue();
        $issueEntity->setProject($this->project);

        return $issueEntity;
    }

    /**
     * @param type $issue
     */
    public function newIssueComment()
    {
        $issueComment = new IssueComment();
        //$issueComment->setProject($this->project);
        return $issueComment;
    }

    public function createIssueComment($issueComment)
    {
        $em = $this->getEntityManager();

        //Set User
        $issueComment->setVerUser($this->currentUser);
        $em->persist($issueComment);
        $em->flush();

        return $issueComment;
    }

    /**
     * @param type $issueEntity
     */
    public function createIssue($issueEntity)
    {
        $em = $this->getEntityManager();
        $issueEntity->setProject($this->project);

        //Set User
        $issueEntity->setVerUser($this->currentUser);

        $em->persist($issueEntity);
        $em->flush();

        return $issueEntity;
    }

    /**
     * @param int $id
     */
    public function reOpenIssue($id)
    {
        $issueEntity = $this->find($id);
        if ($issueEntity) {
            $issueEntity->setOpen();

            $em = $this->getEntityManager();
            $em->flush();
        }

        return $issueEntity;
    }

    /**
     * @param int $id
     */
    public function closeIssue($id)
    {
        $issueEntity = $this->find($id);
        if ($issueEntity) {
            $issueEntity->setClosed();

            $em = $this->getEntityManager();
            $em->flush();
        }

        return $issueEntity;
    }

    /**
     * @param int $issue
     */
    public function updateIssue($issueEntity)
    {
        $em = $this->getEntityManager();

        $em->flush();
    }

    /**
     * Gets the number of Issues for a milestone by state.
     *
     * @param int    $issueMilestoneId
     * @param string $state            open|closed|blank
     */
    public function countIssuesInMilestones($issueMilestoneId, $state)
    {
        return $this->countIssuesForProjectWithStatus($state, false, $issueMilestoneId);
    }

    /**
     * Find issues in milestone.
     *
     * @param int    $issueMilestoneId
     * @param string $state            open|closed|blank
     * @param string $keyword          Search string
     */
    public function findIssuesInMilestones($issueMilestoneId, $state, $keyword = false)
    {
        return $this->findByProjectAndStatus($state, $keyword, $issueMilestoneId, true);
    }

     /**
      * @param type $project
      *
      * @return int
      */
     public function countIssuesForProjectWithStatus($status = 'open', $keyword = false, $milestone = null)
     {
         $qb = $this->findByProjectAndStatus($status, $keyword, $milestone, true);

         $qb->select('count(a)');

         return $qb->getQuery()->getSingleScalarResult();
     }

    public function findByProjectAndStatus($status = 'open', $keyword = false, $milestone = null, $queryOnly = false)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $where = 'a.project = :project';
        $parameters = array('project' => $this->project);

        $qb->select('a')
          ->from('VersionControlGitControlBundle:Issue', 'a')
          ->where($where)
          ->setParameters($parameters);

        if ($status) {
            $qb->andWhere(
               $qb->expr()->like('a.status', ':status')
             )->setParameter('status', $status);
        }
         //If keyword is set
         if ($keyword) {
             $qb->andWhere(
                $qb->expr()->orX(
                    $qb->expr()->like('a.title', ':keyword'),
                    $qb->expr()->like('a.description', ':keyword')
                )
            )->setParameter('keyword', '%'.$keyword.'%');
         }

        if ($milestone != null) {
            $qb->andWhere(
               $qb->expr()->eq('a.issueMilestone', ':milestone')
             )->setParameter('milestone', $milestone);
        }

        $qb->orderBy('a.updatedAt', 'desc');

        if ($queryOnly === true) {
            return $qb;
        } else {
            return $qb->getQuery()->getResult();
        }
    }

    public function getProject()
    {
        return $this->project;
    }

    public function setProject($project)
    {
        $this->project = $project;

        return $this;
    }

    public function getCurrentUser()
    {
        return $this->currentUser;
    }

    public function setCurrentUser($currentUser)
    {
        $this->currentUser = $currentUser;

        return $this;
    }
}
