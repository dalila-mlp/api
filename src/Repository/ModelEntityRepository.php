<?php

namespace App\Repository;

use App\Entity\ModelEntity;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ModelEntity>
 *
 * @method ModelEntity|null find($id, $lockMode = null, $lockVersion = null)
 * @method ModelEntity|null findOneBy(array $criteria, array $orderBy = null)
 * @method ModelEntity[]    findAll()
 * @method ModelEntity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ModelEntityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ModelEntity::class);
    }
}
