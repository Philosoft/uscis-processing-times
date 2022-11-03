<?php

declare(strict_types=1);

namespace App\Dto;

use App\Dto\Partial\ResponseData;

final class ApiResponse
{
    public function __construct(
        public readonly ResponseData $data
    ) {
    }
}
