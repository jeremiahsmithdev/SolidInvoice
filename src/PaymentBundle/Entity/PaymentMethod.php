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

namespace SolidInvoice\PaymentBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Payum\Core\Model\GatewayConfigInterface;
use SolidInvoice\CoreBundle\Traits\Entity\CompanyAware;
use SolidInvoice\CoreBundle\Traits\Entity\TimeStampable;
use SolidInvoice\PaymentBundle\Repository\PaymentMethodRepository;
use Stringable;
use Symfony\Bridge\Doctrine\IdGenerator\UlidGenerator;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation as Serialize;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;
use function array_key_exists;

#[ORM\Table(name: PaymentMethod::TABLE_NAME)]
#[ORM\Entity(repositoryClass: PaymentMethodRepository::class)]
#[UniqueEntity('gatewayName')]
class PaymentMethod implements GatewayConfigInterface, Stringable
{
    final public const TABLE_NAME = 'payment_methods';

    use TimeStampable;
    use CompanyAware;

    #[ORM\Column(name: 'id', type: UlidType::NAME)]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UlidGenerator::class)]
    private ?Ulid $id = null;

    #[ORM\Column(name: 'name', type: Types::STRING, length: 125)]
    #[Assert\NotBlank]
    // #[Serialize\Groups(['payment_api'])]
    private ?string $name = null;

    #[ORM\Column(name: 'gateway_name', type: Types::STRING, length: 125)]
    private ?string $gatewayName = null;

    #[ORM\Column(name: 'factory', type: Types::STRING, length: 125)]
    private ?string $factoryName = null;

    /**
     * @var array<string, string>
     */
    #[ORM\Column(name: 'config', type: 'array', nullable: true)]
    private array $config = [];

    #[ORM\Column(name: 'internal', type: Types::BOOLEAN, nullable: true)]
    private bool $internal = false;

    #[ORM\Column(name: 'enabled', type: Types::BOOLEAN, nullable: true)]
    private bool $enabled;

    /**
     * @var Collection<int, Payment>
     */
    #[ORM\OneToMany(mappedBy: 'method', targetEntity: Payment::class, cascade: ['persist'])]
    private Collection $payments;

    /**
     * Constructor for PaymentMethod.
     * Initializes the payments collection and disables the payment method by default.
     */
    public function __construct()
    {
        $this->payments = new ArrayCollection();
        $this->disable();
    }

    /**
     * Get the unique identifier for the payment method.
     */
    public function getId(): ?Ulid
    {
        return $this->id;
    }

    /**
     * Set the display name of the payment method.
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get the display name of the payment method.
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Get the unique gateway name (e.g., 'bank_transfer', 'paypal_express_checkout').
     */
    public function getGatewayName(): ?string
    {
        return $this->gatewayName;
    }

    /**
     * Set the unique gateway name.
     * @param string $gatewayName The unique identifier for the payment gateway.
     */
    public function setGatewayName($gatewayName): self
    {
        $this->gatewayName = $gatewayName;

        return $this;
    }

    /**
     * Set the configuration array for the payment method.
     * This array holds specific settings for each payment gateway (e.g., bank details for bank transfer).
     * @param array<string, string> $config The configuration data.
     */
    public function setConfig(array $config): self
    {
        $this->config = $config;

        return $this;
    }

    /**
     * Get the configuration array for the payment method.
     * This array holds specific settings for each payment gateway.
     * @return ?array<string, string|null> The configuration data.
     */
    public function getConfig(): ?array
    {
        $config = $this->config;

        if (array_key_exists('sandbox', $config)) {
            $config['sandbox'] = filter_var($config['sandbox'], FILTER_VALIDATE_BOOLEAN);
        }

        $config['factory'] = $this->factoryName;

        return $config;
    }

    /**
     * Check if the payment method is for internal use only.
     */
    public function isInternal(): bool
    {
        return $this->internal;
    }

    /**
     * Set whether the payment method is for internal use only.
     */
    public function setInternal(bool $internal): self
    {
        $this->internal = $internal;

        return $this;
    }

    /**
     * Check if the payment method is enabled.
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Set whether the payment method is enabled.
     */
    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * Enable the payment method.
     */
    public function enable(): self
    {
        $this->enabled = true;

        return $this;
    }

    /**
     * Disable the payment method.
     */
    public function disable(): self
    {
        $this->enabled = false;

        return $this;
    }

    /**
     * Add a payment to this payment method.
     */
    public function addPayment(Payment $payment): self
    {
        $this->payments[] = $payment;

        return $this;
    }

    /**
     * Remove a payment from this payment method.
     */
    public function removePayment(Payment $payment): self
    {
        $this->payments->removeElement($payment);

        return $this;
    }

    /**
     * Get the collection of payments associated with this method.
     * @return Collection<int, Payment> The collection of payments.
     */
    public function getPayments(): Collection
    {
        return $this->payments;
    }

    /**
     * Get the factory name associated with this payment method.
     */
    public function getFactoryName(): ?string
    {
        return $this->factoryName;
    }

    /**
     * Set the factory name for the payment method.
     * @param string $name The factory name.
     */
    public function setFactoryName($name): self
    {
        $this->factoryName = $name;

        return $this;
    }

    /**
     * Check if the payment method is an offline method.
     */
    public function isOffline(): bool
    {
        return 'offline' === $this->factoryName;
    }

    /**
     * Returns the name of the payment method when cast to a string.
     */
    public function __toString(): string
    {
        return $this->name;
    }
}
