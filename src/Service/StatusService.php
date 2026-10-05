<?php

namespace App\Service;

use App\Dto\CreateStatusRequest;
use App\Entity\Status;
use App\Exception\DuplicateStatusNameException;
use App\Exception\StatusInUseException;
use App\Exception\StatusNameNotFoundException;
use App\Exception\StatusNotFoundException;
use App\Exception\SystemStatusDeletionException;
use App\Repository\StatusRepository;
use App\Repository\TaskRepository;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

class StatusService
{
    private StatusRepository $statusRepository;

    private TaskRepository $taskRepository;

    public function __construct(StatusRepository $statusRepository, TaskRepository $taskRepository)
    {
        $this->statusRepository = $statusRepository;
        $this->taskRepository = $taskRepository;
    }

    /**
     * @return Status[]
     */
    public function getAll(): array
    {
        return $this->statusRepository->findAllOrderedById();
    }

    public function getById(int $id): Status
    {
        $status = $this->statusRepository->find($id);

        if (null === $status) {
            throw new StatusNotFoundException($id);
        }

        return $status;
    }

    public function getByName(string $name): Status
    {
        $status = $this->statusRepository->findOneByName($name);

        if (null === $status) {
            throw new StatusNameNotFoundException($name);
        }

        return $status;
    }

    public function create(CreateStatusRequest $request): Status
    {
        $status = new Status();
        $status->setName((string) $request->name);
        $status->setTitle((string) $request->title);
        $status->setSystem(false);

        try {
            $this->statusRepository->save($status);
        } catch (UniqueConstraintViolationException $exception) {
            throw new DuplicateStatusNameException((string) $request->name);
        }

        return $status;
    }

    public function delete(int $id): void
    {
        $status = $this->getById($id);

        if ($status->isSystem()) {
            throw new SystemStatusDeletionException($status->getName());
        }

        $taskCount = $this->taskRepository->countByStatus($status);

        if ($taskCount > 0) {
            throw new StatusInUseException($status->getName(), $taskCount);
        }

        try {
            $this->statusRepository->remove($status);
        } catch (ForeignKeyConstraintViolationException $exception) {
            throw new StatusInUseException($status->getName());
        }
    }
}
