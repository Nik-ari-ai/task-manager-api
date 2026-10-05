<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;

class InvalidJsonException extends \RuntimeException implements ApiException
{
    private string $errorMessage;

    public function __construct(string $errorMessage = 'The request body contains invalid JSON.')
    {
        parent::__construct($errorMessage);

        $this->errorMessage = $errorMessage;
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_BAD_REQUEST;
    }

    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }

    public function getDetails(): array
    {
        return [];
    }
}
