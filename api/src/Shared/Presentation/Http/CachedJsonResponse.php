<?php

declare(strict_types=1);

namespace App\Shared\Presentation\Http;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class CachedJsonResponse
{
    public const MAX_AGE = 30;

    /**
     * @param array<mixed> $data
     */
    public static function public(Request $request, array $data): JsonResponse
    {
        $response = new JsonResponse($data);
        $content = $response->getContent();
        $response->setEtag(hash('sha256', $content === false ? '' : $content));
        $response->setPublic();
        $response->setMaxAge(self::MAX_AGE);
        $response->headers->addCacheControlDirective('must-revalidate');
        $response->isNotModified($request);

        return $response;
    }
}
