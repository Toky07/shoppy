<?php

declare(strict_types=1);

namespace App\Shared\Presentation\Http;

final readonly class ErrorViolation
{
    public function __construct(
        public string $message,
        public ?string $field = null,
    ) {
    }

    /**
     * @return array{message: string, field?: string}
     */
    public function toArray(): array
    {
        $violation = ['message' => $this->message];

        if ($this->field !== null) {
            $violation = ['field' => $this->field] + $violation;
        }

        return $violation;
    }
}
