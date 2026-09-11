<?php

declare(strict_types=1);

namespace App\Shared\Presentation\Http;

final readonly class ErrorResponse
{
    /**
     * @param list<ErrorViolation> $violations
     */
    public function __construct(
        public string $code,
        public string $message,
        public array $violations = [],
    ) {
    }

    /**
     * @return array{error: array{code: string, message: string, violations?: list<array{message: string, field?: string}>}}
     */
    public function toArray(): array
    {
        $error = [
            'code' => $this->code,
            'message' => $this->message,
        ];

        if ($this->violations !== []) {
            $error['violations'] = array_map(
                static fn (ErrorViolation $violation): array => $violation->toArray(),
                $this->violations,
            );
        }

        return ['error' => $error];
    }
}
