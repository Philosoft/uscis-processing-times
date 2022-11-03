<?php

declare(strict_types=1);

namespace App\Dto\Partial;

use Symfony\Component\Serializer\Annotation\SerializedName;

class ResponseData
{
    public function __construct(
        #[SerializedName('processing_time')]
        public ProcessingTime $processingTime
    ) {
    }
}
