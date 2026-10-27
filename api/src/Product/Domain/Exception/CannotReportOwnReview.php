<?php

declare(strict_types=1);

namespace App\Product\Domain\Exception;

use App\Shared\Domain\Exception\ForbiddenException;

final class CannotReportOwnReview extends \RuntimeException implements ForbiddenException
{
    public function __construct()
    {
        parent::__construct('You cannot report your own review.');
    }

    public function errorCode(): string
    {
        return 'cannot_report_own_review';
    }
}
