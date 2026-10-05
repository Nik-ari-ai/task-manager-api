<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;

class SystemStatusDeletionException extends \RuntimeException implements ApiException
{
    private string $statusName;

    public function __construct(string $statusName)
    {
        parent::__construct(sprintf('The system status "%s" cannot be deleted.', $statusName));

        $this->statusName = $statusName;
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_CONFLICT;
    }

    public function getErrorMessage(): string
    {
        return 'A system status cannot be deleted.';
    }

    public function getDetails(): array
    {
        return [
            'status' => $this->statusName,
        ];
    }
}
