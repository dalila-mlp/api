<?php

namespace App\Repository;

use App\Entity\TransactionEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TransactionEntity>
 *
 * @method TransactionEntity|null find($id, $lockMode = null, $lockVersion = null)
 * @method TransactionEntity|null findOneBy(array $criteria, array $orderBy = null)
 * @method TransactionEntity[]    findAll()
 * @method TransactionEntity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TransactionEntityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TransactionEntity::class);
    }
}
