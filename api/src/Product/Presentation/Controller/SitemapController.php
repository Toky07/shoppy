<?php

declare(strict_types=1);

namespace App\Product\Presentation\Controller;

use App\Product\Application\QueryHandler\BuildSitemapQueryHandler;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final readonly class SitemapController
{
    public function __construct(private BuildSitemapQueryHandler $buildSitemap)
    {
    }

    #[Route('/sitemap.xml', methods: ['GET'])]
    public function __invoke(): Response
    {
        $response = new Response($this->buildSitemap->handle(), Response::HTTP_OK, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
        $response->setPublic();
        $response->setMaxAge(300);

        return $response;
    }
}
