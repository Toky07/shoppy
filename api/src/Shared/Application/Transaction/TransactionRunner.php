<?php

declare(strict_types=1);

namespace App\Shared\Application\Transaction;

interface TransactionRunner
{
    /**
     * @template T
     *
     * @param callable(): T $work
     *
     * @return T
     */
    public function run(callable $work): mixed;

    public function afterCommit(object $event): void;
}
