<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;

class StatusInUseException extends \RuntimeException implements ApiException
{
    private string $statusName;

    private ?int $taskCount;

    public function __construct(string $statusName, ?int $taskCount = null)
    {
        if (null === $taskCount) {
            parent::__construct(sprintf('The status "%s" is used by at least one task and cannot be deleted.', $statusName));
        } else {
            parent::__construct(sprintf('The status "%s" is used by %d task(s) and cannot be deleted.', $statusName, $taskCount));
        }

        $this->statusName = $statusName;
        $this->taskCount = $taskCount;
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_CONFLICT;
    }

    public function getErrorMessage(): string
    {
        return 'The status is in use and cannot be deleted.';
    }

    public function getDetails(): array
    {
        $details = [
            'status' => $this->statusName,
        ];

        if (null !== $this->taskCount) {
            $details['tasks_count'] = $this->taskCount;
        }

        return $details;
    }
}
