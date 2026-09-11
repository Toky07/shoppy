<?php

declare(strict_types=1);

namespace App\Shared\Presentation\Exception;

use App\Shared\Presentation\Http\ErrorViolation;
use InvalidArgumentException;

final class InvalidRequest extends InvalidArgumentException
{
    /**
     * @param list<ErrorViolation> $violations
     */
    public function __construct(private array $violations)
    {
        parent::__construct('The request is invalid.');
    }

    public static function of(string $message, ?string $field = null): self
    {
        return new self([new ErrorViolation($message, $field)]);
    }

    /**
     * @return list<ErrorViolation>
     */
    public function violations(): array
    {
        return $this->violations;
    }
}
