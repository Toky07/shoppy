<?php

declare(strict_types=1);

use App\Auth\Application\PasswordHasher;
use App\Auth\Application\TokenGenerator;
use App\Auth\Domain\Entity\AccessToken;
use App\Auth\Domain\Entity\Credentials;
use App\Auth\Domain\Repository\AccessTokenRepository;
use App\Auth\Domain\Repository\CredentialsRepository;
use App\Shared\Domain\Clock;
use App\User\Domain\Entity\User;
use App\User\Domain\Repository\UserRepository;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\Role;
use App\User\Domain\ValueObject\UserId;
use App\Auth\Domain\ValueObject\PlainPassword;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;

final class CatalogHeaderCache
{
    private static \WeakMap $cache;

    public static function get(): \WeakMap
    {
        return self::$cache ??= new \WeakMap();
    }

    public static function clear(): void
    {
        self::$cache = new \WeakMap();
    }
}

function catalogHeaderCache(): \WeakMap
{
    return CatalogHeaderCache::get();
}

function clearCatalogHeaderCache(): void
{
    CatalogHeaderCache::clear();
}

function catalogAdminHeaders(): array
{
    return catalogAuthHeaders('admin@nuvora.test', Role::admin());
}

function catalogCustomerHeaders(): array
{
    return catalogAuthHeaders('customer@nuvora.test', Role::customer());
}

function catalogAuthHeaders(string $emailAddress, Role $role): array
{
    $client = test()->client;
    $cache = catalogHeaderCache();
    $cacheKey = $emailAddress.'|'.$role->value();

    if ($cache->offsetExists($client) && isset($cache[$client][$cacheKey])) {
        return $cache[$client][$cacheKey];
    }

    $container = $client->getContainer();
    $users = $container->get(UserRepository::class);
    $credentialsRepository = $container->get(CredentialsRepository::class);
    $accessTokens = $container->get(AccessTokenRepository::class);
    $passwordHasher = $container->get(PasswordHasher::class);
    $tokenGenerator = $container->get(TokenGenerator::class);
    $clock = $container->get(Clock::class);

    $email = Email::fromString($emailAddress);
    $user = $users->findByEmail($email);

    if ($user === null) {
        $user = User::register(UserId::generate(), $email, $clock->now(), $role);
        $users->save($user);
        $credentialsRepository->save(Credentials::create(
            $user->id(),
            $passwordHasher->hash(PlainPassword::fromString('secret-secret')),
        ));
    } elseif ($user->role()->value() !== $role->value()) {
        $user->assignRole($role);
        $users->save($user);
    }

    $generated = $tokenGenerator->generate();
    $now = $clock->now();
    $accessTokens->save(AccessToken::issue(
        $user->id(),
        $generated->hash,
        $now->modify('+7 days'),
        $now,
    ));

    $headers = ['HTTP_AUTHORIZATION' => 'Bearer '.$generated->plain];
    $entry = $cache->offsetExists($client) ? $cache[$client] : [];
    $entry[$cacheKey] = $headers;
    $cache[$client] = $entry;

    return $headers;
}

function registerCatalogUser(KernelBrowser $client, string $email): array
{
    $client->jsonRequest('POST', '/users', [
        'email' => $email,
        'password' => 'secret-secret',
    ]);

    return json_decode((string) $client->getResponse()->getContent(), true, flags: JSON_THROW_ON_ERROR);
}
