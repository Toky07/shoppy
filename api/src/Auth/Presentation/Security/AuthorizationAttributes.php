<?php

declare(strict_types=1);

namespace App\Auth\Presentation\Security;

use App\Auth\Infrastructure\Security\AuthenticatedUser;
use App\Media\Presentation\Security\MediaVoter;
use App\Order\Presentation\Security\OrderVoter;
use App\Payment\Presentation\Security\PaymentVoter;
use App\Product\Presentation\Security\ProductVoter;
use App\User\Presentation\Security\UserVoter;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;

/**
 * Registry of Symfony authorization attributes used by the API.
 *
 * @see api/docs/authorization.md
 */
final class AuthorizationAttributes
{
    public const IS_AUTHENTICATED = AuthenticatedVoter::IS_AUTHENTICATED;

    public const ROLE_ADMIN = AuthenticatedUser::ROLE_ADMIN;

    public const ROLE_USER = AuthenticatedUser::ROLE_USER;

    public const ORDER_VIEW = OrderVoter::VIEW;

    public const ORDER_CANCEL = OrderVoter::CANCEL;

    public const USER_VIEW = UserVoter::VIEW;

    public const PAYMENT_ACCESS = PaymentVoter::ACCESS;

    public const PRODUCT_ADMIN = ProductVoter::ADMIN;

    public const MEDIA_LIST = MediaVoter::LIST;

    private function __construct()
    {
    }
}
