<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ValidationException extends \RuntimeException implements ApiException
{
    /**
     * @var array<int, array<string, string>>
     */
    private array $violations = [];

    public function __construct(ConstraintViolationListInterface $violationList)
    {
        parent::__construct('Validation failed.');

        foreach ($violationList as $violation) {
            $this->violations[] = [
                'field' => $violation->getPropertyPath(),
                'message' => (string) $violation->getMessage(),
            ];
        }
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_UNPROCESSABLE_ENTITY;
    }

    public function getErrorMessage(): string
    {
        return 'Validation failed.';
    }

    public function getDetails(): array
    {
        return [
            'violations' => $this->violations,
        ];
    }
}
