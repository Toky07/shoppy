<?php

declare(strict_types=1);

use App\Auth\Domain\Exception\Forbidden;
use App\Auth\Domain\Exception\InvalidCredentials;
use App\Auth\Domain\Exception\Unauthenticated;
use App\Product\Domain\Exception\InvalidProductName;
use App\Product\Domain\Exception\ProductNotFound;
use App\Shared\Presentation\Exception\InvalidRequest;
use App\Shared\Presentation\Http\ApiExceptionMapper;
use App\User\Domain\Exception\EmailAlreadyRegistered;
use App\User\Domain\ValueObject\Email;
use Symfony\Component\HttpFoundation\Exception\JsonException as HttpJsonException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

it('maps a not found domain exception without leaking internals', function () {
    $mapped = (new ApiExceptionMapper())->map(
        new ProductNotFound('550e8400-e29b-41d4-a716-446655440000'),
    );

    expect($mapped->status)->toBe(404)
        ->and($mapped->unexpected)->toBeFalse()
        ->and($mapped->body->toArray())->toBe([
            'error' => [
                'code' => 'product_not_found',
                'message' => 'The requested resource was not found.',
            ],
        ])
        ->and(json_encode($mapped->body->toArray()))->not->toContain('550e8400');
});

it('maps a domain validation exception to violations', function () {
    $mapped = (new ApiExceptionMapper())->map(new InvalidProductName());

    expect($mapped->status)->toBe(400)
        ->and($mapped->body->toArray())->toBe([
            'error' => [
                'code' => 'validation_error',
                'message' => 'The request is invalid.',
                'violations' => [
                    [
                        'field' => 'name',
                        'message' => 'Product name cannot be empty.',
                    ],
                ],
            ],
        ]);
});

it('maps an invalid request to violations', function () {
    $mapped = (new ApiExceptionMapper())->map(InvalidRequest::of('This field is required.', 'name'));

    expect($mapped->status)->toBe(400)
        ->and($mapped->body->toArray()['error']['code'])->toBe('validation_error')
        ->and($mapped->body->toArray()['error']['violations'])->toBe([
            ['field' => 'name', 'message' => 'This field is required.'],
        ]);
});

it('maps invalid json without leaking parser details', function () {
    $mapped = (new ApiExceptionMapper())->map(new JsonException('Syntax error'));

    expect($mapped->status)->toBe(400)
        ->and($mapped->body->toArray())->toBe([
            'error' => [
                'code' => 'invalid_json',
                'message' => 'The request body is not valid JSON.',
            ],
        ])
        ->and(json_encode($mapped->body->toArray()))->not->toContain('Syntax error');
});

it('maps kernel-wrapped invalid json without leaking parser details', function () {
    $mapped = (new ApiExceptionMapper())->map(
        new BadRequestHttpException(
            'Could not decode request body.',
            new HttpJsonException('Could not decode request body.', 0, new JsonException('Syntax error')),
        ),
    );

    expect($mapped->status)->toBe(400)
        ->and($mapped->body->toArray())->toBe([
            'error' => [
                'code' => 'invalid_json',
                'message' => 'The request body is not valid JSON.',
            ],
        ])
        ->and(json_encode($mapped->body->toArray()))->not->toContain('Could not decode')
        ->and(json_encode($mapped->body->toArray()))->not->toContain('Syntax error');
});

it('maps an unexpected exception to a generic internal error', function () {
    $mapped = (new ApiExceptionMapper())->map(new RuntimeException('SQLSTATE password=secret'));

    expect($mapped->status)->toBe(500)
        ->and($mapped->unexpected)->toBeTrue()
        ->and($mapped->body->toArray())->toBe([
            'error' => [
                'code' => 'internal_error',
                'message' => 'An unexpected error occurred.',
            ],
        ])
        ->and(json_encode($mapped->body->toArray()))->not->toContain('SQLSTATE')
        ->and(json_encode($mapped->body->toArray()))->not->toContain('secret');
});

it('maps a missing route without leaking the path', function () {
    $mapped = (new ApiExceptionMapper())->map(new NotFoundHttpException('No route found for "GET /admin"'));

    expect($mapped->status)->toBe(404)
        ->and($mapped->body->toArray()['error']['code'])->toBe('not_found')
        ->and(json_encode($mapped->body->toArray()))->not->toContain('/admin');
});

it('maps a conflict without leaking internals', function () {
    $mapped = (new ApiExceptionMapper())->map(
        new EmailAlreadyRegistered(Email::fromString('ada@nuvora.test')),
    );

    expect($mapped->status)->toBe(409)
        ->and($mapped->body->toArray())->toBe([
            'error' => [
                'code' => 'email_already_registered',
                'message' => 'The request conflicts with the current state.',
            ],
        ])
        ->and(json_encode($mapped->body->toArray()))->not->toContain('ada@nuvora.test');
});

it('maps invalid credentials without leaking internals', function () {
    $mapped = (new ApiExceptionMapper())->map(new InvalidCredentials());

    expect($mapped->status)->toBe(401)
        ->and($mapped->unexpected)->toBeFalse()
        ->and($mapped->body->toArray())->toBe([
            'error' => [
                'code' => 'invalid_credentials',
                'message' => 'The provided credentials are invalid.',
            ],
        ]);
});

it('maps a missing authentication without leaking internals', function () {
    $mapped = (new ApiExceptionMapper())->map(new Unauthenticated());

    expect($mapped->status)->toBe(401)
        ->and($mapped->body->toArray())->toBe([
            'error' => [
                'code' => 'unauthenticated',
                'message' => 'The request is not authenticated.',
            ],
        ]);
});

it('maps a forbidden access without leaking internals', function () {
    $mapped = (new ApiExceptionMapper())->map(new Forbidden());

    expect($mapped->status)->toBe(403)
        ->and($mapped->unexpected)->toBeFalse()
        ->and($mapped->body->toArray())->toBe([
            'error' => [
                'code' => 'forbidden',
                'message' => 'The request is not authorized.',
            ],
        ]);
});
