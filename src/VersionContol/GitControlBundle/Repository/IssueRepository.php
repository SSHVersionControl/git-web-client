<?php
namespace VersionContol\GitControlBundle\Repository;

use Doctrine\ORM\EntityRepository;
use VersionContol\GitControlBundle\Repository\Issues\IssueRepositoryInterface;
use VersionContol\GitControlBundle\Entity\Issue;
use VersionContol\GitControlBundle\Entity\IssueComment;

class IssueRepository extends EntityRepository implements IssueRepositoryInterface
{
    

    protected $project;
    
    protected $currentUser;


    /**
     * Finds issues for a state
     * @param string $keyword
     * @return array of issues
     */
    public function findIssues($keyword,$state="open"){
        
        return $this->findByProjectAndStatus($state,$keyword,null,true);

    }
    
    /**
     * Finds issues for a state
     * @param string $keyword
     * @return array of issues
     */
    public function countFindIssues($keyword,$state="open"){
        return $this->countIssuesForProjectWithStatus($state,$keyword);
    }
    
    /**
     * 
     * @param integer $id
     */
    public function findIssueById($id){

        $issueEntity = $this->find($id);

        if (!$issueEntity) {
            throw $this->createNotFoundException('Unable to find Issue entity.');
        }
        
        return $issueEntity;
    }
    
    /**
     * 
     * @param type $issue
     */
    public function newIssue(){
        $issueEntity = new Issue();
        $issueEntity->setProject($this->project);
        return $issueEntity;
    }
    
    /**
     * 
     * @param type $issue
     */
    public function newIssueComment(){
        $issueComment = new IssueComment();
        //$issueComment->setProject($this->project);
        return $issueComment;
    }
    
    /**
     * 
     * @param type $issueEntity
     */
    public function createIssue($issueEntity){
        $em=$this->getEntityManager();
        $issueEntity->setProject($this->project);
        
        //Set User
        $issueEntity->setVerUser($this->currentUser);

        $em->persist($issueEntity);
        $em->flush();
        
        return $issueEntity;
    }
    
    /**
     * 
     * @param integer $id
     */
    public function reOpenIssue($id){
        $issueEntity = $this->find($id);
        if($issueEntity){
            $issueEntity->setOpen();

            $em=$this->getEntityManager();
            $em->flush();
        }
        return $issueEntity;
    }
    
    /**
     * 
     * @param integer $id
     */
    public function closeIssue($id){
        $issueEntity = $this->find($id);
        if($issueEntity){
            $issueEntity->setClosed();

            $em=$this->getEntityManager();
            $em->flush();
        }
        return $issueEntity;
    }
    
    /**
     * 
     * @param integer $issue
     */
    public function updateIssue($issueEntity){
        $em=$this->getEntityManager();
        
        $em->flush();
    }
    
    /**
     * 
     * @param type $project
     * @return integer
     */
     public function countIssuesForProjectWithStatus($status = 'open',$keyword=false,$milestone=null)
     {

       $qb = $this->findByProjectAndStatus($status,$keyword,$milestone,true);

       $qb->select('count(a)');

       return $qb->getQuery()->getSingleScalarResult();

     }

     public function findByProjectAndStatus($status = 'open',$keyword = false,$milestone=null,$queryOnly= false){
        $em=$this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $where = 'a.project = :project';
        $parameters = array('project'=>$this->project);


        $qb->select('a')
          ->from('VersionContolGitControlBundle:Issue','a')
          ->where($where)
          ->setParameters($parameters);

        if($status){
             $qb->andWhere(
               $qb->expr()->like('a.status', ':status')
             )->setParameter('status', $status);
        }
         //If keyword is set 
         if($keyword){
           $qb->andWhere(
               $qb->expr()->like('a.title', ':keyword')
           )->setParameter('keyword', '%'.$keyword.'%')
           ->andWhere(
               $qb->expr()->like('a.description', ':keyword')
           )->setParameter('keyword', '%'.$keyword.'%');
         }

         if($milestone != null){
             $qb->andWhere(
               $qb->expr()->eq('a.issueMilestone', ':milestone')
             )->setParameter('milestone', $milestone);
        }

        $qb->orderBy('a.updatedAt','desc');

        if($queryOnly === true){
            return $qb;
        }else{
            return $qb->getQuery()->getResult();
        }

     }
     
     public function getProject() {
         return $this->project;
     }

     public function setProject($project) {
         $this->project = $project;
         return $this;
     }
     
     public function getCurrentUser() {
         return $this->currentUser;
     }

     public function setCurrentUser($currentUser) {
         $this->currentUser = $currentUser;
         return $this;
     }




  
}
?>