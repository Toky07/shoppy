<?php

declare(strict_types=1);

use App\Shared\Domain\Exception\RateLimited;
use App\Shared\Infrastructure\Http\FileRateLimiter;
use App\Shared\Presentation\EventSubscriber\RateLimitSubscriber;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

function rateLimitRequestEvent(Request $request): RequestEvent
{
    $kernel = new class implements HttpKernelInterface {
        public function handle(Request $request, int $type = self::MAIN_REQUEST, bool $catch = true): Response
        {
            return new Response();
        }
    };

    return new RequestEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST);
}

function accountRateLimitSubscriber(int $accountLimit = 2, int $loginLimit = 20): RateLimitSubscriber
{
    $directory = sys_get_temp_dir().'/shoppy-rate-limit-'.bin2hex(random_bytes(8));
    mkdir($directory);

    return new RateLimitSubscriber(
        new FileRateLimiter($directory),
        $loginLimit,
        10,
        30,
        120,
        $accountLimit,
        60,
    );
}

it('rate limits password reset, email verification and token confirmation routes', function (string $path) {
    $subscriber = accountRateLimitSubscriber();
    $event = rateLimitRequestEvent(Request::create($path, 'POST', server: ['REMOTE_ADDR' => '203.0.113.8']));

    $subscriber->onRequest($event);
    $subscriber->onRequest($event);

    expect(fn () => $subscriber->onRequest($event))->toThrow(RateLimited::class);
})->with([
    '/auth/password-resets',
    '/auth/password-resets/confirm',
    '/auth/email-verifications',
    '/auth/email-verifications/request',
    '/auth/email-changes/confirm',
]);

it('does not apply the account limit to login', function () {
    $subscriber = accountRateLimitSubscriber(accountLimit: 1, loginLimit: 20);
    $event = rateLimitRequestEvent(Request::create('/auth/login', 'POST', server: ['REMOTE_ADDR' => '203.0.113.8']));

    $subscriber->onRequest($event);
    $subscriber->onRequest($event);

    expect(true)->toBeTrue();
});
