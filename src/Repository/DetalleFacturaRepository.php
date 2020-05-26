<?php

namespace App\Repository;

use App\Entity\DetalleFactura;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method DetalleFactura|null find($id, $lockMode = null, $lockVersion = null)
 * @method DetalleFactura|null findOneBy(array $criteria, array $orderBy = null)
 * @method DetalleFactura[]    findAll()
 * @method DetalleFactura[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DetalleFacturaRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DetalleFactura::class);
    }

    // /**
    //  * @return DetalleFactura[] Returns an array of DetalleFactura objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?DetalleFactura
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
