<?php

declare(strict_types=1);

namespace App\Entity;

use App\Repository\I485EntryRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: I485EntryRepository::class)]
#[ORM\Index(fields: ['createdAt'])]
class I485Entry
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private string $processingCenter = '';

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank]
    private string $rawResponse = '';

    #[ORM\Column]
    private float $waitTime = -255.0;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private DateTimeImmutable $createdAt;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private DateTimeImmutable $publicationDate;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    private DateTimeImmutable $serviceRequestDate;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private string $officeCode = '';

    public function __construct()
    {
        $now = new DateTimeImmutable();

        $this->createdAt = $now;
        $this->publicationDate = $now;
        $this>$this->serviceRequestDate = $now;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProcessingCenter(): string
    {
        return $this->processingCenter;
    }

    public function setProcessingCenter(string $processingCenter): self
    {
        $this->processingCenter = $processingCenter;

        return $this;
    }

    public function getRawResponse(): string
    {
        return $this->rawResponse;
    }

    public function setRawResponse(string $rawResponse): self
    {
        $this->rawResponse = $rawResponse;

        return $this;
    }

    public function getWaitTime(): float
    {
        return $this->waitTime;
    }

    public function setWaitTime(float $waitTime): self
    {
        $this->waitTime = $waitTime;

        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getPublicationDate(): DateTimeImmutable
    {
        return $this->publicationDate;
    }

    public function setPublicationDate(DateTimeImmutable $publicationDate): self
    {
        $this->publicationDate = $publicationDate;

        return $this;
    }

    public function getServiceRequestDate(): DateTimeImmutable
    {
        return $this->serviceRequestDate;
    }

    public function setServiceRequestDate(DateTimeImmutable $serviceRequestDate): self
    {
        $this->serviceRequestDate = $serviceRequestDate;

        return $this;
    }

    public function getOfficeCode(): string
    {
        return $this->officeCode;
    }

    public function setOfficeCode(string $officeCode): self
    {
        $this->officeCode = $officeCode;

        return $this;
    }
}
