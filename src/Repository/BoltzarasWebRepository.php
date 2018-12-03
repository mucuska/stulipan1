<?php

namespace App\Repository;

use App\Entity\BoltzarasWeb;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;


class BoltzarasWebRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, BoltzarasWeb::class);
    }

    /**
     * @param $start
     * @param $end
     * @return \Doctrine\ORM\Query
     */
    public function sumAllBetweenDates($start, $end)
    {
        $qb = $this->createQueryBuilder('p')
            ->where('p.month >= :start')
            ->andWhere('p.month <= :end')
            ->setParameter('start', $start)
            ->setParameter('end', $end)
            ->orderBy('p.month', 'DESC')
            ->select('SUM(p.amount) as webshopforgalom')
            ->getQuery()
        ;
        return $qb;
    }

    /**
     * @return \Doctrine\ORM\Query
     */
    public function sumAllQueryBuilder()
    {
        return $this->createQueryBuilder('p')
            ->select('SUM(p.amount) as webshopforgalom')
            ->getQuery()
            ;
    }
}