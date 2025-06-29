<?php

declare(strict_types=1);

/*
 * This file is part of SolidInvoice project.
 *
 * (c) Pierre du Plessis <open-source@solidworx.co>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace SolidInvoice\JobBundle\Entity;

use ApiPlatform\Metadata\ApiProperty;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use SolidInvoice\ClientBundle\Entity\Client;
use SolidInvoice\CoreBundle\Traits\Entity\CompanyAware;
use SolidInvoice\CoreBundle\Traits\Entity\TimeStampable;
use SolidInvoice\InvoiceBundle\Entity\Invoice;
use SolidInvoice\JobBundle\Repository\JobRepository;
use SolidInvoice\QuoteBundle\Entity\Quote;
use Stringable;
use Symfony\Bridge\Doctrine\IdGenerator\UlidGenerator;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Component\Serializer\Attribute as Serialize;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Get(),
        new Post(),
        new GetCollection(),
        new Patch(),
        new Delete(),
    ],
    normalizationContext: [
        'groups' => ['job_api:read'],
        AbstractObjectNormalizer::SKIP_NULL_VALUES => false,
        AbstractObjectNormalizer::SKIP_UNINITIALIZED_VALUES => true,
    ],
    denormalizationContext: [
        'groups' => ['job_api:write'],
    ],
)]
#[ORM\Table(name: Job::TABLE_NAME)]
#[ORM\Entity(repositoryClass: JobRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Job implements Stringable
{
    final public const TABLE_NAME = 'jobs';
    
    final public const STATUS_PENDING = 'pending';
    final public const STATUS_IN_PROGRESS = 'in_progress';
    final public const STATUS_DONE = 'done';
    
    use TimeStampable;
    use CompanyAware;

    #[ORM\Column(name: 'id', type: UlidType::NAME)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UlidGenerator::class)]
    #[Serialize\Groups(['job_api:read'])]
    private ?Ulid $id = null;

    #[ORM\ManyToOne(targetEntity: Quote::class)]
    #[ORM\JoinColumn(name: 'quote_id', referencedColumnName: 'id', nullable: false)]
    #[Assert\NotNull]
    #[Serialize\Groups(['job_api:read', 'job_api:write'])]
    private ?Quote $quote = null;

    #[ORM\ManyToOne(targetEntity: Client::class)]
    #[ORM\JoinColumn(name: 'client_id', referencedColumnName: 'id', nullable: false)]
    #[Assert\NotNull]
    #[Serialize\Groups(['job_api:read', 'job_api:write'])]
    private ?Client $client = null;

    #[ORM\Column(name: 'status', type: Types::STRING, length: 25)]
    #[Assert\NotBlank]
    #[Assert\Choice(choices: [self::STATUS_PENDING, self::STATUS_IN_PROGRESS, self::STATUS_DONE])]
    #[Serialize\Groups(['job_api:read', 'job_api:write'])]
    #[ApiProperty(writable: true)]
    private string $status = self::STATUS_PENDING;

    #[ORM\Column(name: 'description', type: Types::TEXT, nullable: true)]
    #[Serialize\Groups(['job_api:read', 'job_api:write'])]
    private ?string $description = null;

    #[ORM\Column(name: 'scheduled_date', type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Serialize\Groups(['job_api:read', 'job_api:write'])]
    private ?DateTimeInterface $scheduledDate = null;

    #[ORM\ManyToOne(targetEntity: Invoice::class, inversedBy: 'jobs')]
    #[ORM\JoinColumn(name: 'invoice_id', referencedColumnName: 'id', nullable: true)]
    #[Serialize\Groups(['job_api:read', 'job_api:write'])]
    private ?Invoice $invoice = null;

    public function __construct()
    {
        $this->status = self::STATUS_PENDING;
    }

    public function getId(): ?Ulid
    {
        return $this->id;
    }

    public function getQuote(): ?Quote
    {
        return $this->quote;
    }

    public function setQuote(?Quote $quote): self
    {
        $this->quote = $quote;
        return $this;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): self
    {
        $this->client = $client;
        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getScheduledDate(): ?DateTimeInterface
    {
        return $this->scheduledDate;
    }

    public function setScheduledDate(?DateTimeInterface $scheduledDate): self
    {
        $this->scheduledDate = $scheduledDate;
        return $this;
    }

    public function getInvoice(): ?Invoice
    {
        return $this->invoice;
    }

    public function setInvoice(?Invoice $invoice): self
    {
        $this->invoice = $invoice;
        return $this;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isInProgress(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    public function isDone(): bool
    {
        return $this->status === self::STATUS_DONE;
    }

    public function __toString(): string
    {
        return $this->description ?? ('Job for Quote #' . $this->quote?->getId());
    }
}