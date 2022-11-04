<?php

declare(strict_types=1);

namespace App\Dto\Partial\FormOffices;

use Symfony\Component\Serializer\Annotation\SerializedName;

final class OfficesWrapper
{
    public function __construct(
        #[SerializedName('form_name')]
        public readonly string $formName,
        #[SerializedName('form_type')]
        public readonly string $formType,
        /** @var Office[] $offices */
        #[SerializedName('offices')]
        public readonly array $offices
    ) {
    }
}
