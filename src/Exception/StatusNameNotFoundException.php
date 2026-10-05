<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;

class StatusNameNotFoundException extends \RuntimeException implements ApiException
{
    private string $statusName;

    public function __construct(string $statusName)
    {
        parent::__construct(sprintf('Status with name "%s" was not found.', $statusName));

        $this->statusName = $statusName;
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
            'status' => $this->statusName,
        ];
    }
}
