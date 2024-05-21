<?php

namespace App\Repository;

use App\Entity\DatafileEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DatafileEntity>
 *
 * @method DatafileEntity|null find($id, $lockMode = null, $lockVersion = null)
 * @method DatafileEntity|null findOneBy(array $criteria, array $orderBy = null)
 * @method DatafileEntity[]    findAll()
 * @method DatafileEntity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DatafileEntityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DatafileEntity::class);
    }
}
