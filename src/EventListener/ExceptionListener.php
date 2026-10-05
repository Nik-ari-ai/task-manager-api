<?php

namespace App\EventListener;

use App\Exception\ApiException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ExceptionListener
{
    private bool $debug;

    public function __construct(bool $debug = false)
    {
        $this->debug = $debug;
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $request = $event->getRequest();

        if (!str_starts_with($request->getPathInfo(), '/api')) {
            return;
        }

        $throwable = $event->getThrowable();

        if ($throwable instanceof ApiException) {
            $event->setResponse($this->buildApiExceptionResponse($throwable));

            return;
        }

        if ($throwable instanceof HttpExceptionInterface) {
            $event->setResponse($this->buildHttpExceptionResponse($throwable));

            return;
        }

        $event->setResponse($this->buildGenericResponse($throwable));
    }

    private function buildApiExceptionResponse(ApiException $exception): JsonResponse
    {
        $payload = [
            'error' => $exception->getErrorMessage(),
        ];

        $details = $exception->getDetails();

        if ([] !== $details) {
            $payload['details'] = $details;
        }

        return new JsonResponse($payload, $exception->getStatusCode());
    }

    private function buildHttpExceptionResponse(HttpExceptionInterface $exception): JsonResponse
    {
        $statusCode = $exception->getStatusCode();

        $payload = [
            'error' => Response::$statusTexts[$statusCode] ?? 'HTTP error.',
        ];

        if ($this->debug) {
            $payload['details'] = [
                'message' => $exception->getMessage(),
            ];
        }

        return new JsonResponse($payload, $statusCode);
    }

    private function buildGenericResponse(\Throwable $throwable): JsonResponse
    {
        $payload = [
            'error' => 'An internal server error occurred.',
        ];

        if ($this->debug) {
            $payload['details'] = [
                'message' => $throwable->getMessage(),
                'class' => $throwable::class,
            ];
        }

        return new JsonResponse($payload, Response::HTTP_INTERNAL_SERVER_ERROR);
    }
}
