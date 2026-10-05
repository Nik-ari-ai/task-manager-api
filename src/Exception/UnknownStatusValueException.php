<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;

class UnknownStatusValueException extends \RuntimeException implements ApiException
{
    private string $statusName;

    public function __construct(string $statusName)
    {
        parent::__construct(sprintf('The status value "%s" cannot be applied because it does not exist.', $statusName));

        $this->statusName = $statusName;
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_UNPROCESSABLE_ENTITY;
    }

    public function getErrorMessage(): string
    {
        return 'The provided status value cannot be applied.';
    }

    public function getDetails(): array
    {
        return [
            'status' => $this->statusName,
        ];
    }
}
