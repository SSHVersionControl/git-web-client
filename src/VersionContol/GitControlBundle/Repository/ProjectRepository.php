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
        $qb->andWhere(
            $qb->expr()->like('a.title', ':keyword')
        )->setParameter('keyword', '%'.$keyword.'%')
        ->andWhere(
            $qb->expr()->like('a.description', ':keyword')
        )->setParameter('keyword', '%'.$keyword.'%');
      }
    
     if($queryOnly === true){
         return $qb;
     }else{
         return $qb->getQuery()->getResult();
     }
    
  }
}
