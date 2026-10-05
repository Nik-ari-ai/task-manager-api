<?php

namespace App\Service;

use App\Entity\Status;
use App\Entity\Task;

class EntityPresenter
{
    private const DATE_FORMAT = \DateTimeInterface::ATOM;

    /**
     * @return array<string, mixed>
     */
    public function presentTask(Task $task): array
    {
        return [
            'id' => $task->getId(),
            'title' => $task->getTitle(),
            'description' => $task->getDescription(),
            'status' => $this->presentStatus($task->getStatus()),
            'created_at' => $task->getCreatedAt()->format(self::DATE_FORMAT),
            'updated_at' => $task->getUpdatedAt()->format(self::DATE_FORMAT),
        ];
    }

    /**
     * @param Task[] $tasks
     *
     * @return array<int, array<string, mixed>>
     */
    public function presentTaskList(array $tasks): array
    {
        $result = [];

        foreach ($tasks as $task) {
            $result[] = $this->presentTask($task);
        }

        return $result;
    }

    /**
     * @return array<string, mixed>
     */
    public function presentStatus(Status $status): array
    {
        return [
            'id' => $status->getId(),
            'name' => $status->getName(),
            'title' => $status->getTitle(),
            'is_system' => $status->isSystem(),
        ];
    }

    /**
     * @param Status[] $statuses
     *
     * @return array<int, array<string, mixed>>
     */
    public function presentStatusList(array $statuses): array
    {
        $result = [];

        foreach ($statuses as $status) {
            $result[] = $this->presentStatus($status);
        }

        return $result;
    }
}
