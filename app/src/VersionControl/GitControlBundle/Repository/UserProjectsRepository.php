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

class UserProjectsRepository extends EntityRepository
{
    public function findByUserAndKeyword($user, $keyword = false, $queryOnly = false)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('a')
       ->from('VersionControlGitControlBundle:UserProjects', 'a')
        ->leftJoin('a.project', 'b')
        ->where('a.user = :user')
        ->setParameter('user', $user);

      //If keyword is set
      if ($keyword) {
          $qb->andWhere(' b.title LIKE :keyword OR b.description LIKE :keyword ')
          ->setParameter('keyword', '%'.$keyword.'%');
      }

        if ($queryOnly === true) {
            return $qb;
        } else {
            return $qb->getQuery()->getResult();
        }
    }
}
