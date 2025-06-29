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

namespace SolidInvoice\JobBundle\Test\Factory;

use SolidInvoice\ClientBundle\Test\Factory\ClientFactory;
use SolidInvoice\JobBundle\Entity\Job;
use SolidInvoice\QuoteBundle\Test\Factory\QuoteFactory;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;
use Zenstruck\Foundry\Persistence\Proxy;

/**
 * @extends PersistentProxyObjectFactory<Job>
 *
 * @method        Job|Proxy                             create(array|callable $attributes = [])
 * @method static Job|Proxy                             createOne(array $attributes = [])
 * @method static Job|Proxy                             find(object|array|mixed $criteria)
 * @method static Job|Proxy                             findOrCreate(array $attributes)
 * @method static Job|Proxy                             first(string $sortedField = 'id')
 * @method static Job|Proxy                             last(string $sortedField = 'id')
 * @method static Job|Proxy                             random(array $attributes = [])
 * @method static Job|Proxy                             randomOrCreate(array $attributes = [])
 * @method static Job[]|Proxy[]                         all()
 * @method static Job[]|Proxy[]                         createMany(int $number, array|callable $attributes = [])
 * @method static Job[]|Proxy[]                         createSequence(array|callable $sequence)
 * @method static Job[]|Proxy[]                         findBy(array $attributes)
 * @method static Job[]|Proxy[]                         randomRange(int $min, int $max, array $attributes = [])
 * @method static Job[]|Proxy[]                         randomSet(int $number, array $attributes = [])
 */
final class JobFactory extends PersistentProxyObjectFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     */
    public function __construct()
    {
    }

    public static function class(): string
    {
        return Job::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     */
    protected function defaults(): array|callable
    {
        return [
            'quote' => QuoteFactory::new(),
            'client' => ClientFactory::new(),
            'status' => Job::STATUS_PENDING,
            'description' => self::faker()->sentence(),
            'scheduledDate' => self::faker()->optional()->dateTimeBetween('now', '+1 month'),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(Job $job): void {})
        ;
    }
}