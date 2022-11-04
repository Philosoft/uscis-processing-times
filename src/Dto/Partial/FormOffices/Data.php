<?php

declare(strict_types=1);

namespace App\Dto\Partial\FormOffices;

use Symfony\Component\Serializer\Annotation\SerializedName;

final class Data
{
    public function __construct(
        #[SerializedName('form_offices')]
        public readonly OfficesWrapper $wrapper
    ) {
    }
}
