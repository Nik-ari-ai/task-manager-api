<?php

namespace App\Exception;

interface ApiException extends \Throwable
{
    public function getStatusCode(): int;

    public function getErrorMessage(): string;

    /**
     * @return array<string, mixed>
     */
    public function getDetails(): array;
}
