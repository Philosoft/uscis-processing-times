<?php

declare(strict_types=1);

namespace App\Dto\Partial;

use Symfony\Component\Serializer\Annotation\SerializedName;

final class Subtype
{
    public function __construct(
        #[SerializedName('form_type')]
        public readonly string $formType,
        #[SerializedName('publication_date')]
        public readonly string $publicationDate,
        #[SerializedName('service_request_date')]
        public readonly string $serviceRequestDate,
        /** @var Range[] $range */
        public readonly array $range,
    ) {
    }
}
