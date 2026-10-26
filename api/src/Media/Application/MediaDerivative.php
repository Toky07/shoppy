<?php

declare(strict_types=1);

namespace App\Media\Application;

enum MediaDerivative: string
{
    case Thumb = 'thumb';
    case Card = 'card';
    case Detail = 'detail';

    public function maxWidth(): int
    {
        return match ($this) {
            self::Thumb => 128,
            self::Card => 600,
            self::Detail => 1200,
        };
    }

    public function pathFor(string $relativePath): string
    {
        $dot = strrpos($relativePath, '.');

        if ($dot === false) {
            return $relativePath.'.'.$this->value;
        }

        return substr($relativePath, 0, $dot).'.'.$this->value.substr($relativePath, $dot);
    }
}
