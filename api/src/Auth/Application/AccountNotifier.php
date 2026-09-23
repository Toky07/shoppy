<?php

declare(strict_types=1);

namespace App\Auth\Application;

use App\Email\Domain\Event\EmailRequested;
use App\Email\Domain\ValueObject\EmailAddress;
use App\Email\Domain\ValueObject\EmailBody;
use App\Email\Domain\ValueObject\EmailSubject;
use App\Shared\Domain\Event\EventBus;

final readonly class AccountNotifier
{
    public function __construct(
        private EventBus $events,
        private string $frontendBaseUrl,
    ) {
    }

    public function sendPasswordReset(string $email, string $token): void
    {
        $this->send(
            $email,
            'Réinitialisation du mot de passe',
            "Pour choisir un nouveau mot de passe, ouvrez ce lien. Il expire dans une heure :\n".$this->link('/reset-password', $token),
        );
    }

    public function sendEmailVerification(string $email, string $token): void
    {
        $this->send(
            $email,
            'Confirmez votre adresse email',
            "Confirmez votre adresse pour sécuriser votre compte :\n".$this->link('/verify-email', $token),
        );
    }

    public function sendEmailAlreadyRegistered(string $email): void
    {
        $this->send(
            $email,
            'Votre compte existe déjà',
            "Un compte utilise déjà cette adresse. Connectez-vous, ou demandez un nouveau mot de passe si vous ne vous en souvenez plus.\n".rtrim($this->frontendBaseUrl, '/').'/login',
        );
    }

    public function sendEmailChange(string $email, string $token): void
    {
        $this->send(
            $email,
            'Confirmez votre nouvelle adresse',
            "Confirmez cette adresse pour l'utiliser sur votre compte :\n".$this->link('/confirm-email', $token),
        );
    }

    private function send(string $email, string $subject, string $text): void
    {
        $this->events->publish(new EmailRequested(
            EmailAddress::fromString($email),
            EmailSubject::fromString($subject),
            EmailBody::fromText($text),
        ));
    }

    private function link(string $path, string $token): string
    {
        return rtrim($this->frontendBaseUrl, '/').$path.'?token='.rawurlencode($token);
    }
}
