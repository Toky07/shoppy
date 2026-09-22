<?php

declare(strict_types=1);

namespace App\Email\Domain\ValueObject;

use App\Email\Domain\Exception\InvalidEmailBody;

final readonly class EmailBody
{
    private function __construct(private string $text, private ?string $html)
    {
    }

    public static function fromText(string $text, ?string $html = null): self
    {
        $normalizedText = trim($text);

        if ($normalizedText === '') {
            throw new InvalidEmailBody();
        }

        $normalizedHtml = $html === null ? null : trim($html);

        return new self($normalizedText, $normalizedHtml === '' ? null : $normalizedHtml);
    }

    public function text(): string
    {
        return $this->text;
    }

    public function html(): ?string
    {
        return $this->html;
    }
}
