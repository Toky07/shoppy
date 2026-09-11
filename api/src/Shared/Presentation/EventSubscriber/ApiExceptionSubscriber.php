<?php

declare(strict_types=1);

namespace App\Shared\Presentation\EventSubscriber;

use App\Shared\Presentation\Http\ApiExceptionMapper;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;

final readonly class ApiExceptionSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private ApiExceptionMapper $mapper,
        private LoggerInterface $logger,
    ) {
    }

    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::EXCEPTION => ['onException', 10]];
    }

    public function onException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        $mapped = $this->mapper->map($exception);

        if ($mapped->unexpected) {
            $this->logger->error($exception->getMessage(), ['exception' => $exception]);
        }

        $event->setResponse(new JsonResponse($mapped->body->toArray(), $mapped->status));
    }
}
