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

namespace SolidInvoice\MailerBundle\Tests\Configurator;

use PHPUnit\Framework\TestCase;
use SolidInvoice\MailerBundle\Configurator\BrevoConfigurator;
use SolidInvoice\MailerBundle\Form\Type\TransportConfig\KeyTransportConfigType;
use Symfony\Component\Mailer\Transport\Dsn;

/**
 * @covers \SolidInvoice\MailerBundle\Configurator\BrevoConfigurator
 */
final class BrevoConfiguratorTest extends TestCase
{
    public function testGetName(): void
    {
        $configurator = new BrevoConfigurator();
        self::assertSame('Brevo', $configurator->getName());
    }

    public function testGetForm(): void
    {
        $configurator = new BrevoConfigurator();
        self::assertSame(KeyTransportConfigType::class, $configurator->getForm());
    }

    public function testConfigure(): void
    {
        $configurator = new BrevoConfigurator();
        $config = ['key' => 'test-api-key'];

        $dsn = $configurator->configure($config);

        self::assertEquals(Dsn::fromString('brevo+api://test-api-key@default'), $dsn);
    }
}