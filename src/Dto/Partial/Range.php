<?php

declare(strict_types=1);

namespace App\Dto\Partial;

use Symfony\Component\Serializer\Annotation\SerializedName;

final class Range
{
    public function __construct(
        public readonly string $unit,
        public readonly float $value
    ) {
    }
}
