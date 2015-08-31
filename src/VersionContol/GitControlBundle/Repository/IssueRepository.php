<?php
namespace VersionContol\GitControlBundle\Repository;

use Doctrine\ORM\EntityRepository;

class IssueRepository extends EntityRepository
{
    

 /**
  * 
  * @param type $project
  * @return integer
  */
  public function countIssuesForProjectWithStatus($project,$status = 'open')
  {
      
    $em=$this->getEntityManager();
    $qb = $em->createQueryBuilder();
    $qb->select('count(a)')
       ->from('VersionContolGitControlBundle:Issue','a')
       ->where('a.project = :project AND a.status = :open')
       ->setParameters(array('project'=>$project,'open'=>$status));
    
    return $qb->getQuery()->getSingleScalarResult();
    
  }
  
}
?>