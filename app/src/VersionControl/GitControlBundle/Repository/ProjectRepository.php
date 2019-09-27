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

class ProjectRepository extends EntityRepository
{
    public function findByKeyword($keyword = false, $queryOnly = false)
    {
        $em = $this->getEntityManager();
        $qb = $em->createQueryBuilder();

        $qb->select('a')
       ->from('VersionControlGitControlBundle:Project', 'a');

      //If keyword is set
      if ($keyword) {
          $qb->andWhere('a.title LIKE :keyword OR a.title LIKE :keyword')
          ->setParameter('keyword', '%'.$keyword.'%');
      }

        if ($queryOnly === true) {
            return $qb;
        } else {
            return $qb->getQuery()->getResult();
        }
    }
}
