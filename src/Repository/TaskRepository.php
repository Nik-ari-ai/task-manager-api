<?php

namespace App\Repository;

use App\Entity\Status;
use App\Entity\Task;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Task>
 */
class TaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
    }

    public function save(Task $task): void
    {
        $entityManager = $this->getEntityManager();

        $entityManager->persist($task);
        $entityManager->flush();
    }

    public function remove(Task $task): void
    {
        $entityManager = $this->getEntityManager();

        $entityManager->remove($task);
        $entityManager->flush();
    }

    public function flush(): void
    {
        $this->getEntityManager()->flush();
    }

    /**
     * @return Task[]
     */
    public function findAllOrderedById(): array
    {
        return $this->createQueryBuilder('task')
            ->orderBy('task.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Task[]
     */
    public function findByStatusOrderedById(Status $status): array
    {
        return $this->createQueryBuilder('task')
            ->andWhere('task.status = :status')
            ->setParameter('status', $status)
            ->orderBy('task.id', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function countByStatus(Status $status): int
    {
        return (int) $this->createQueryBuilder('task')
            ->select('COUNT(task.id)')
            ->andWhere('task.status = :status')
            ->setParameter('status', $status)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
