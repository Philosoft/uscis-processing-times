<?php

declare(strict_types=1);

namespace App\Dto\Partial;

use Symfony\Component\Serializer\Annotation\SerializedName;

final class ProcessingTime
{
    public function __construct(
        #[SerializedName('form_name')]
        public readonly string $formName,
        #[SerializedName('office_code')]
        public readonly string $officeCode,

        /** @var Subtype[] $subtypes */
        public readonly array $subtypes,
    ) {
    }
}
