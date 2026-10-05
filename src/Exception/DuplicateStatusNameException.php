<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;

class DuplicateStatusNameException extends \RuntimeException implements ApiException
{
    private string $statusName;

    public function __construct(string $statusName)
    {
        parent::__construct(sprintf('A status with the name "%s" already exists.', $statusName));

        $this->statusName = $statusName;
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_CONFLICT;
    }

    public function getErrorMessage(): string
    {
        return 'A status with this name already exists.';
    }

    public function getDetails(): array
    {
        return [
            'name' => $this->statusName,
        ];
    }
}
