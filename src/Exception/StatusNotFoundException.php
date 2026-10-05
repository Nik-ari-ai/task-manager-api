<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;

class StatusNotFoundException extends \RuntimeException implements ApiException
{
    private int $statusId;

    public function __construct(int $statusId)
    {
        parent::__construct(sprintf('Status with id %d was not found.', $statusId));

        $this->statusId = $statusId;
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_NOT_FOUND;
    }

    public function getErrorMessage(): string
    {
        return 'Status not found.';
    }

    public function getDetails(): array
    {
        return [
            'status_id' => $this->statusId,
        ];
    }
}
