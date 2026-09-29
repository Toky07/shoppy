<?php

declare(strict_types=1);

namespace App\Media\Presentation\Http\ValueResolver;

use App\Media\Presentation\Request\ListMediaHttpRequest;
use App\Media\Presentation\Security\MediaListSubject;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final readonly class MediaListArgumentValueResolver implements ValueResolverInterface
{
    private const HTTP_REQUEST_ATTRIBUTE = '_list_media_http_request';

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $type = $argument->getType();

        if ($type !== ListMediaHttpRequest::class && $type !== MediaListSubject::class) {
            return [];
        }

        $httpRequest = $request->attributes->get(self::HTTP_REQUEST_ATTRIBUTE);

        if (!$httpRequest instanceof ListMediaHttpRequest) {
            $httpRequest = ListMediaHttpRequest::fromRequest($request);
            $request->attributes->set(self::HTTP_REQUEST_ATTRIBUTE, $httpRequest);
        }

        if ($type === ListMediaHttpRequest::class) {
            yield $httpRequest;

            return;
        }

        yield new MediaListSubject($httpRequest->ownerType);
    }
}
