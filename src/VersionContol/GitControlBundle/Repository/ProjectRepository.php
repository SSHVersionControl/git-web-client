<?php
namespace VersionContol\GitControlBundle\Repository;

use Doctrine\ORM\EntityRepository;

class ProjectRepository extends EntityRepository
{

    public function findByKeyword($keyword = false,$queryOnly= false){
     $em=$this->getEntityManager();
     $qb = $em->createQueryBuilder();
 
     
     $qb->select('a')
       ->from('VersionContolGitControlBundle:Project','a');
     

      //If keyword is set 
      if($keyword){
         $qb->andWhere('a.title LIKE :keyword OR a.title LIKE :keyword')
          ->setParameter('keyword', '%'.$keyword.'%');
      }
    
     if($queryOnly === true){
         return $qb;
     }else{
         return $qb->getQuery()->getResult();
     }
    
  }
}
