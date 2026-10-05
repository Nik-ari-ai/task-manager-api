<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;

class TaskNotFoundException extends \RuntimeException implements ApiException
{
    private int $taskId;

    public function __construct(int $taskId)
    {
        parent::__construct(sprintf('Task with id %d was not found.', $taskId));

        $this->taskId = $taskId;
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_NOT_FOUND;
    }

    public function getErrorMessage(): string
    {
        return 'Task not found.';
    }

    public function getDetails(): array
    {
        return [
            'task_id' => $this->taskId,
        ];
    }
}
