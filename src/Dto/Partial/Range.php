<?php

declare(strict_types=1);

namespace App\Dto\Partial;

final class Range
{
    public function __construct(
        public readonly string $unit,
        public readonly float $value
    ) {
    }
}
