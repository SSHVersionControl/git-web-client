<?php
namespace VersionContol\GitControlBundle\Repository;

use Doctrine\ORM\EntityRepository;
use VersionContol\GitControlBundle\Repository\Issues\IssueMilestoneRepositoryInterface;
use VersionContol\GitControlBundle\Entity\IssueMilestone;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class IssueMilestoneRepository extends EntityRepository implements IssueMilestoneRepositoryInterface
{
    /**
     *
     * @var VersionContol\GitControlBundle\Entity\Project
     */
    protected $project;
    
    /**
     * @var VersionContol\GitControlBundle\Entity\User\User
     */
    protected $currentUser;
     
    /**
     * Get Project
     */
    public function getProject() {
        return $this->project;
    }
    
    /**
     * Set Project 
     */
    public function setProject($project) {
        $this->project = $project;
        return $this;
    }
    
    /**
     * Get Current User
     */
    public function getCurrentUser() {
        return $this->currentUser;
    }

    /**
     * Set Current User
     */
    public function setCurrentUser($currentUser) {
        $this->currentUser = $currentUser;
        return $this;
    }
    
    /**
     * Finds all milestones
     * @param string $keyword
     * @return array of issues
     */
    public function listMilestones($state = 'open'){
        return $this->findByProjectAndStatus($state,true);
    }
    
    /**
     * Get number of milestones for a state
     * @param string $state open|closed
     */
    public function countMilestones($state){
        return $this->countForProjectWithStatus($state);
    }
     
    /**
     * Find milestone for Id
     * @param integer $id
     */
    public function findMilestoneById($id){
        $issueMilestone = $this->find($id);
        if($issueMilestone){
            if($issueMilestone->getProject()->getId() !== $this->getProject()->getId()){
                throw new AccessDeniedException("Milestones project id is different to request project Id");
            }
        }
        return $issueMilestone;
    }
    
    /**
     * Gets a new Milestone entity
     * 
     * @return VersionContol\GitControlBundle\Entity\IssueMilestone
     */
    public function newMilestone(){
        $issueMilestoneEntity = new IssueMilestone();
        return $issueMilestoneEntity;
    }
    
    /**
     * 
     * @param VersionContol\GitControlBundle\Entity\IssueMilestone $issueMilestone
     */
    public function createMilestone($issueMilestone){
         $em=$this->getEntityManager();
        
        //Set User
        $issueMilestone->setVerUser($this->currentUser);
        //Set Project
        $issueMilestone->setProject($this->project);
        
        $em->persist($issueMilestone);
        $em->flush();
         
        return $issueMilestone;
    }

    
    /**
     * 
     * @param VersionContol\GitControlBundle\Entity\IssueMilestone $issueMilestone
     */
    public function updateMilestone($issueMilestone){
        if($issueMilestone->getProject()->getId() !== $this->getProject()->getId()){
            throw new AccessDeniedException("Milestones project id is different to request project Id");
        }
        
        $em=$this->getEntityManager();
        $em->flush(); 
        return $issueMilestone;
    }
    
    /**
     * 
     * @param integer $issueMilestoneId
     */
    public function deleteMilestone($id){
        
        $em=$this->getEntityManager();
        $issueMilestone = $this->findMilestoneById($id);
        if($issueMilestone){ 
            $em->remove($issueMilestone);
            $em->flush();
        }
    }
    
    
    /**
     * 
     * @param integer $id
     */
    public function reOpenMilestone($id){
        $em=$this->getEntityManager();
        $issueMilestone = $this->findMilestoneById($id);
        if($issueMilestone){ 
            $issueMilestone->setOpen();
            $em->flush();
        }
        return $issueMilestone;
    }
    
    /**
     * 
     * @param integer $id
     */
    public function closeMilestone($id){
        $em=$this->getEntityManager();
        $issueMilestone = $this->findMilestoneById($id);
        if($issueMilestone){ 
            $issueMilestone->setClosed();
            $em->flush();
        }
        return $issueMilestone;
    }
    
 /**
  * 
  * @param type $project
  * @return integer
  */
  public function countForProjectWithStatus($status = 'open')
  {
      
    $qb = $this->findByProjectAndStatus($status,true);
    
    $qb->select('count(a)');
    
    return $qb->getQuery()->getSingleScalarResult();
    
  }
  
  public function findByProjectAndStatus($status = 'open',$queryOnly= false){
     $em=$this->getEntityManager();
     $qb = $em->createQueryBuilder();
     
     $where = 'a.project = :project';
     $parameters = array('project'=>$this->project);
     

     $qb->select('a')
       ->from('VersionContolGitControlBundle:IssueMilestone','a')
       ->where($where)
       ->setParameters($parameters);
     
     if($status){
          $qb->andWhere(
            $qb->expr()->like('a.state', ':status')
          )->setParameter('status', $status);
     }

     if($queryOnly === true){
         return $qb;
     }else{
         return $qb->getQuery()->getResult();
     }
    
  }
  
}
?>