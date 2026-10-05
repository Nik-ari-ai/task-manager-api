<?php

namespace App\Service;

use App\Dto\CreateTaskRequest;
use App\Dto\UpdateTaskStatusRequest;
use App\Entity\Task;
use App\Exception\StatusNameNotFoundException;
use App\Exception\TaskNotFoundException;
use App\Exception\UnknownStatusValueException;
use App\Repository\StatusRepository;
use App\Repository\TaskRepository;

class TaskService
{
    private const DEFAULT_STATUS_NAME = 'new';

    private TaskRepository $taskRepository;

    private StatusRepository $statusRepository;

    public function __construct(TaskRepository $taskRepository, StatusRepository $statusRepository)
    {
        $this->taskRepository = $taskRepository;
        $this->statusRepository = $statusRepository;
    }

    /**
     * @return Task[]
     */
    public function getAll(): array
    {
        return $this->taskRepository->findAllOrderedById();
    }

    /**
     * @return Task[]
     */
    public function getFilteredByStatusName(string $statusName): array
    {
        $status = $this->statusRepository->findOneByName($statusName);

        if (null === $status) {
            throw new StatusNameNotFoundException($statusName);
        }

        return $this->taskRepository->findByStatusOrderedById($status);
    }

    public function getById(int $id): Task
    {
        $task = $this->taskRepository->find($id);

        if (null === $task) {
            throw new TaskNotFoundException($id);
        }

        return $task;
    }

    public function create(CreateTaskRequest $request): Task
    {
        $defaultStatus = $this->statusRepository->findOneByName(self::DEFAULT_STATUS_NAME);

        if (null === $defaultStatus) {
            throw new \RuntimeException(sprintf(
                'The default status "%s" is missing. Run the database migrations before creating tasks.',
                self::DEFAULT_STATUS_NAME
            ));
        }

        $task = new Task();
        $task->setTitle((string) $request->title);
        $task->setDescription(null === $request->description ? null : (string) $request->description);
        $task->setStatus($defaultStatus);

        $this->taskRepository->save($task);

        return $task;
    }

    public function updateStatus(int $id, UpdateTaskStatusRequest $request): Task
    {
        $task = $this->getById($id);

        $statusName = (string) $request->status;

        $status = $this->statusRepository->findOneByName($statusName);

        if (null === $status) {
            throw new UnknownStatusValueException($statusName);
        }

        $task->setStatus($status);
        $task->touch();

        $this->taskRepository->flush();

        return $task;
    }

    public function delete(int $id): void
    {
        $task = $this->getById($id);

        $this->taskRepository->remove($task);
    }
}
