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

namespace SolidInvoice\MailerBundle\Tests\Integration;

use PHPUnit\Framework\TestCase;
use SolidInvoice\MailerBundle\Configurator\BrevoConfigurator;
use SolidInvoice\MailerBundle\Factory\MailerConfigFactory;
use SolidInvoice\SettingsBundle\SystemConfig;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Transport\TransportInterface;

/**
 * @covers \SolidInvoice\MailerBundle\Configurator\BrevoConfigurator
 * @covers \SolidInvoice\MailerBundle\Factory\MailerConfigFactory
 */
final class BrevoIntegrationTest extends TestCase
{
    public function testBrevoConfiguratorWithInvalidConfig(): void
    {
        $brevoConfigurator = new BrevoConfigurator();
        $systemConfig = $this->createMock(SystemConfig::class);
        $transport = new Transport([]);

        // Mock the system config to return invalid configuration
        $systemConfig->expects($this->once())
            ->method('get')
            ->with(MailerConfigFactory::CONFIG_KEY)
            ->willReturn(json_encode([
                'provider' => 'InvalidProvider',
                'config' => ['key' => 'test-api-key']
            ]));

        $factory = new MailerConfigFactory($transport, $systemConfig, [$brevoConfigurator]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Invalid mailer config');

        $factory->fromStrings([]);
    }

    public function testBrevoConfiguratorProperties(): void
    {
        $brevoConfigurator = new BrevoConfigurator();

        self::assertSame('Brevo', $brevoConfigurator->getName());
        self::assertSame('SolidInvoice\MailerBundle\Form\Type\TransportConfig\KeyTransportConfigType', $brevoConfigurator->getForm());
    }

    public function testBrevoConfiguratorGeneratesCorrectDsn(): void
    {
        $brevoConfigurator = new BrevoConfigurator();
        $config = ['key' => 'test-api-key-123'];

        $dsn = $brevoConfigurator->configure($config);

        self::assertSame('brevo+api', $dsn->getScheme());
        self::assertSame('test-api-key-123', $dsn->getUser());
        self::assertSame('default', $dsn->getHost());
    }
}