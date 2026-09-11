<?php

declare(strict_types=1);

namespace App\Shared\Presentation\Http;

use App\Shared\Domain\Exception\AuthenticationFailed;
use App\Shared\Domain\Exception\ConflictException;
use App\Shared\Domain\Exception\ForbiddenException;
use App\Shared\Domain\Exception\InvalidValue;
use App\Shared\Domain\Exception\NotFoundException;
use App\Shared\Domain\Exception\UnauthenticatedException;
use App\Shared\Presentation\Exception\InvalidRequest;
use JsonException;
use Symfony\Component\HttpFoundation\Exception\JsonException as HttpJsonException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

final class ApiExceptionMapper
{
    public function map(Throwable $exception): MappedHttpError
    {
        if ($exception instanceof NotFoundException) {
            return new MappedHttpError(
                new ErrorResponse($exception->errorCode(), 'The requested resource was not found.'),
                404,
            );
        }

        if ($exception instanceof ConflictException) {
            return new MappedHttpError(
                new ErrorResponse($exception->errorCode(), 'The request conflicts with the current state.'),
                409,
            );
        }

        if ($exception instanceof AuthenticationFailed) {
            return new MappedHttpError(
                new ErrorResponse($exception->errorCode(), 'The provided credentials are invalid.'),
                401,
            );
        }

        if ($exception instanceof UnauthenticatedException) {
            return new MappedHttpError(
                new ErrorResponse($exception->errorCode(), 'The request is not authenticated.'),
                401,
            );
        }

        if ($exception instanceof ForbiddenException) {
            return new MappedHttpError(
                new ErrorResponse($exception->errorCode(), 'The request is not authorized.'),
                403,
            );
        }

        if ($exception instanceof InvalidValue) {
            return new MappedHttpError(
                new ErrorResponse(
                    'validation_error',
                    'The request is invalid.',
                    [new ErrorViolation($exception->getMessage(), $exception->field())],
                ),
                400,
            );
        }

        if ($exception instanceof InvalidRequest) {
            return new MappedHttpError(
                new ErrorResponse('validation_error', 'The request is invalid.', $exception->violations()),
                400,
            );
        }

        if ($this->isInvalidJson($exception)) {
            return new MappedHttpError(
                new ErrorResponse('invalid_json', 'The request body is not valid JSON.'),
                400,
            );
        }

        if ($exception instanceof NotFoundHttpException) {
            return new MappedHttpError(
                new ErrorResponse('not_found', 'The requested resource was not found.'),
                404,
            );
        }

        if ($exception instanceof MethodNotAllowedHttpException) {
            return new MappedHttpError(
                new ErrorResponse('method_not_allowed', 'The HTTP method is not allowed.'),
                405,
            );
        }

        if ($exception instanceof HttpExceptionInterface && $exception->getStatusCode() < 500) {
            return new MappedHttpError(
                new ErrorResponse('http_error', 'The request cannot be processed.'),
                $exception->getStatusCode(),
            );
        }

        return new MappedHttpError(
            new ErrorResponse('internal_error', 'An unexpected error occurred.'),
            500,
            unexpected: true,
        );
    }

    private function isInvalidJson(Throwable $exception): bool
    {
        if ($exception instanceof JsonException || $exception instanceof HttpJsonException) {
            return true;
        }

        $previous = $exception->getPrevious();

        return $exception instanceof BadRequestHttpException
            && ($previous instanceof JsonException || $previous instanceof HttpJsonException);
    }
}
