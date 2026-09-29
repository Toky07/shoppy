<?php

declare(strict_types=1);

namespace App\User\Presentation\Http\ValueResolver;

use App\User\Presentation\Security\UserAccessSubject;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final readonly class UserAccessSubjectValueResolver implements ValueResolverInterface
{
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if ($argument->getType() !== UserAccessSubject::class) {
            return [];
        }

        $userId = $request->attributes->get('id');

        if (!is_string($userId) || $userId === '') {
            return [];
        }

        yield new UserAccessSubject($userId);
    }
}
