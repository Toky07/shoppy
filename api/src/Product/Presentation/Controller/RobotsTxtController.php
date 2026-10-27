<?php

declare(strict_types=1);

namespace App\Product\Presentation\Controller;

use App\Product\Application\RobotsTxt;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final readonly class RobotsTxtController
{
    public function __construct(private RobotsTxt $robots)
    {
    }

    #[Route('/robots.txt', methods: ['GET'])]
    public function __invoke(): Response
    {
        $response = new Response($this->robots->render(), Response::HTTP_OK, [
            'Content-Type' => 'text/plain; charset=UTF-8',
        ]);
        $response->setPublic();
        $response->setMaxAge(300);

        return $response;
    }
}
