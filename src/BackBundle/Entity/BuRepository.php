<?php

namespace BackBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * BuRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class BuRepository extends EntityRepository
{
    public function bySalary($salary) {
        $qb = $this->createQueryBuilder('bu')
                ->where('bu.salary = :salary')
                ->setParameter('salary', $salary);
        return $qb->getQuery()->getResult();
    }
}
