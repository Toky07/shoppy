<?php

declare(strict_types=1);

namespace App\Email\Domain\ValueObject;

use App\Email\Domain\Exception\InvalidEmailAttachment;

final readonly class EmailAttachment
{
    private const int MAX_BYTES = 10 * 1024 * 1024;

    private const int MAX_FILENAME_LENGTH = 150;

    private function __construct(
        private string $filename,
        private string $mimeType,
        private string $content,
    ) {
    }

    public static function fromContent(string $filename, string $mimeType, string $content): self
    {
        return new self(
            self::normalizeFilename($filename),
            self::normalizeMimeType($mimeType),
            self::normalizeContent($content),
        );
    }

    public function filename(): string
    {
        return $this->filename;
    }

    public function mimeType(): string
    {
        return $this->mimeType;
    }

    public function content(): string
    {
        return $this->content;
    }

    private static function normalizeFilename(string $filename): string
    {
        $normalized = trim($filename);

        if (
            $normalized === ''
            || $normalized === '.'
            || $normalized === '..'
            || str_contains($normalized, "\n")
            || str_contains($normalized, "\r")
            || str_contains($normalized, "\0")
            || str_contains($normalized, '/')
            || str_contains($normalized, '\\')
            || mb_strlen($normalized) > self::MAX_FILENAME_LENGTH
        ) {
            throw new InvalidEmailAttachment();
        }

        return $normalized;
    }

    private static function normalizeMimeType(string $mimeType): string
    {
        $normalized = strtolower(trim($mimeType));

        if (preg_match('/^[a-z0-9][a-z0-9.+-]*\/[a-z0-9][a-z0-9.+-]*$/', $normalized) !== 1) {
            throw new InvalidEmailAttachment();
        }

        return $normalized;
    }

    private static function normalizeContent(string $content): string
    {
        if ($content === '' || strlen($content) > self::MAX_BYTES) {
            throw new InvalidEmailAttachment();
        }

        return $content;
    }
}
