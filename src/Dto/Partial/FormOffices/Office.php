<?php

declare(strict_types=1);

namespace App\Dto\Partial\FormOffices;

use Symfony\Component\Serializer\Annotation\SerializedName;

final class Office
{
    public function __construct(
        #[SerializedName('office_code')]
        public readonly string $code,
        #[SerializedName('office_description')]
        public readonly string $description
    ) {
    }
}
