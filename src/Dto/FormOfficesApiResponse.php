<?php

declare(strict_types=1);

namespace App\Dto;

use App\Dto\Partial\FormOffices\Data;

final class FormOfficesApiResponse
{
    public function __construct(
        public readonly Data $data
    ) {
    }
}
