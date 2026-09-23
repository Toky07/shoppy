<?php

declare(strict_types=1);

namespace App\Auth\Presentation\Controller;

use App\Auth\Application\Command\ChangePasswordCommand;
use App\Auth\Application\Command\ConfirmEmailChangeCommand;
use App\Auth\Application\Command\DeleteAccountCommand;
use App\Auth\Application\Command\LogoutAllCommand;
use App\Auth\Application\Command\RequestEmailChangeCommand;
use App\Auth\Application\Command\RequestEmailVerificationCommand;
use App\Auth\Application\Command\RequestPasswordResetCommand;
use App\Auth\Application\Command\ResetPasswordCommand;
use App\Auth\Application\Command\VerifyEmailCommand;
use App\Auth\Application\CommandHandler\ChangePasswordCommandHandler;
use App\Auth\Application\CommandHandler\ConfirmEmailChangeCommandHandler;
use App\Auth\Application\CommandHandler\DeleteAccountCommandHandler;
use App\Auth\Application\CommandHandler\LogoutAllCommandHandler;
use App\Auth\Application\CommandHandler\RequestEmailChangeCommandHandler;
use App\Auth\Application\CommandHandler\RequestEmailVerificationCommandHandler;
use App\Auth\Application\CommandHandler\RequestPasswordResetCommandHandler;
use App\Auth\Application\CommandHandler\ResetPasswordCommandHandler;
use App\Auth\Application\CommandHandler\VerifyEmailCommandHandler;
use App\Auth\Presentation\Http\CurrentUser;
use App\Auth\Presentation\Http\SessionCookie;
use App\Auth\Presentation\Request\AccountTokenHttpRequest;
use App\Auth\Presentation\Request\ChangePasswordHttpRequest;
use App\Auth\Presentation\Request\EmailChangeHttpRequest;
use App\Auth\Presentation\Request\EmailHttpRequest;
use App\Auth\Presentation\Request\PasswordHttpRequest;
use App\Auth\Presentation\Request\ResetPasswordHttpRequest;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final readonly class AccountSecurityController
{
    public function __construct(
        private CurrentUser $currentUser,
        private SessionCookie $sessionCookie,
        private RequestPasswordResetCommandHandler $requestPasswordReset,
        private ResetPasswordCommandHandler $resetPassword,
        private VerifyEmailCommandHandler $verifyEmail,
        private RequestEmailVerificationCommandHandler $requestEmailVerification,
        private ChangePasswordCommandHandler $changePassword,
        private RequestEmailChangeCommandHandler $requestEmailChange,
        private ConfirmEmailChangeCommandHandler $confirmEmailChange,
        private DeleteAccountCommandHandler $deleteAccount,
        private LogoutAllCommandHandler $logoutAll,
    ) {
    }

    #[Route('/auth/password-resets', methods: ['POST'])]
    public function requestPasswordReset(Request $request): Response
    {
        $httpRequest = EmailHttpRequest::fromPayload($request->toArray());
        $this->requestPasswordReset->handle(new RequestPasswordResetCommand($httpRequest->email));

        return new Response(status: Response::HTTP_NO_CONTENT);
    }

    #[Route('/auth/password-resets/confirm', methods: ['POST'])]
    public function confirmPasswordReset(Request $request): Response
    {
        $httpRequest = ResetPasswordHttpRequest::fromPayload($request->toArray());
        $this->resetPassword->handle(new ResetPasswordCommand($httpRequest->token, $httpRequest->password));

        return new Response(status: Response::HTTP_NO_CONTENT);
    }

    #[Route('/auth/email-verifications', methods: ['POST'])]
    public function verifyEmail(Request $request): Response
    {
        $httpRequest = AccountTokenHttpRequest::fromPayload($request->toArray());
        $this->verifyEmail->handle(new VerifyEmailCommand($httpRequest->token));

        return new Response(status: Response::HTTP_NO_CONTENT);
    }

    #[Route('/auth/email-verifications/request', methods: ['POST'])]
    public function requestEmailVerification(Request $request): Response
    {
        $userId = $this->currentUser->id();
        $this->requestEmailVerification->handle(new RequestEmailVerificationCommand($userId));

        return new Response(status: Response::HTTP_NO_CONTENT);
    }

    #[Route('/auth/password', methods: ['POST'])]
    public function changePassword(Request $request): Response
    {
        $userId = $this->currentUser->id();
        $httpRequest = ChangePasswordHttpRequest::fromPayload($request->toArray());
        $this->changePassword->handle(new ChangePasswordCommand(
            $userId,
            $httpRequest->currentPassword,
            $httpRequest->newPassword,
        ));

        return $this->withoutSession($request);
    }

    #[Route('/auth/email-changes', methods: ['POST'])]
    public function requestEmailChange(Request $request): Response
    {
        $userId = $this->currentUser->id();
        $httpRequest = EmailChangeHttpRequest::fromPayload($request->toArray());
        $this->requestEmailChange->handle(new RequestEmailChangeCommand(
            $userId,
            $httpRequest->email,
            $httpRequest->currentPassword,
        ));

        return new Response(status: Response::HTTP_NO_CONTENT);
    }

    #[Route('/auth/email-changes/confirm', methods: ['POST'])]
    public function confirmEmailChange(Request $request): Response
    {
        $httpRequest = AccountTokenHttpRequest::fromPayload($request->toArray());
        $this->confirmEmailChange->handle(new ConfirmEmailChangeCommand($httpRequest->token));

        return $this->withoutSession($request);
    }

    #[Route('/auth/logout-all', methods: ['POST'])]
    public function logoutAll(Request $request): Response
    {
        $userId = $this->currentUser->id();
        $this->logoutAll->handle(new LogoutAllCommand($userId));

        return $this->withoutSession($request);
    }

    #[Route('/auth/account/deletion', methods: ['POST'])]
    public function deleteAccount(Request $request): Response
    {
        $userId = $this->currentUser->id();
        $httpRequest = PasswordHttpRequest::fromPayload($request->toArray());
        $this->deleteAccount->handle(new DeleteAccountCommand($userId, $httpRequest->password));

        return $this->withoutSession($request);
    }

    private function withoutSession(Request $request): Response
    {
        $response = new Response(status: Response::HTTP_NO_CONTENT);
        $this->sessionCookie->clear($response, $request);

        return $response;
    }
}
