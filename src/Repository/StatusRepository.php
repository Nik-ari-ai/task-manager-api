<?php

namespace App\Repository;

use App\Entity\Status;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Status>
 */
class StatusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Status::class);
    }

    public function save(Status $status): void
    {
        $entityManager = $this->getEntityManager();

        $entityManager->persist($status);
        $entityManager->flush();
    }

    public function remove(Status $status): void
    {
        $entityManager = $this->getEntityManager();

        $entityManager->remove($status);
        $entityManager->flush();
    }

    /**
     * @return Status[]
     */
    public function findAllOrderedById(): array
    {
        return $this->createQueryBuilder('status')
            ->orderBy('status.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findOneByName(string $name): ?Status
    {
        return $this->findOneBy(['name' => $name]);
    }
}
