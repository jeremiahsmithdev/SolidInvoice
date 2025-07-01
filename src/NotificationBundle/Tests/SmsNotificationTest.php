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

namespace SolidInvoice\NotificationBundle\Tests;

use Hamcrest\Core\IsEqual;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery as M;
use PHPUnit\Framework\TestCase;
use SolidInvoice\CoreBundle\Test\Traits\FakerTestTrait;
use SolidInvoice\InstallBundle\Test\EnsureApplicationInstalled;
use SolidInvoice\NotificationBundle\Attribute\AsNotification;
use SolidInvoice\NotificationBundle\Configurator\ConfiguratorInterface;
use SolidInvoice\NotificationBundle\Entity\TransportSetting;
use SolidInvoice\NotificationBundle\Entity\UserNotification;
use SolidInvoice\NotificationBundle\Notification\NotificationManager;
use SolidInvoice\NotificationBundle\Notification\NotificationMessage;
use SolidInvoice\UserBundle\Entity\User;
use Symfony\Component\DependencyInjection\ServiceLocator;
use Symfony\Component\Notifier\Message\SmsMessage;
use Symfony\Component\Notifier\Notification\SmsNotificationInterface;
use Symfony\Component\Notifier\NotifierInterface;
use Symfony\Component\Notifier\Recipient\Recipient;
use Symfony\Component\Notifier\Recipient\SmsRecipientInterface;
use Symfony\Component\Notifier\Transport\Dsn;
use Twig\Environment;

/**
 * @covers \SolidInvoice\NotificationBundle\Notification\NotificationManager
 */
final class SmsNotificationTest extends TestCase
{
    use EnsureApplicationInstalled;
    use FakerTestTrait;
    use MockeryPHPUnitIntegration;

    private NotificationManager $notificationManager;
    private NotifierInterface | M\MockInterface $notifier;

    protected function setUp(): void
    {
        parent::setUp();

        $this->notifier = M::mock(NotifierInterface::class);

        $this->notificationManager = new NotificationManager(
            $this->notifier,
            static::getContainer()->get('doctrine')->getRepository(UserNotification::class),
            new ServiceLocator([]),
        );
    }

    public function testSendSmsNotification(): void
    {
        // Create a test notification class that supports SMS
        $class = new #[AsNotification(name: 'test_sms_event')] class extends NotificationMessage implements SmsNotificationInterface {
            public function getTextContent(Environment $twig): string
            {
                return 'Invoice #INV-001 status changed to Paid. Thank you for your business!';
            }

            public function asSmsMessage(SmsRecipientInterface $recipient, ?string $transport = null): SmsMessage
            {
                return new SmsMessage(
                    $recipient->getPhone(),
                    $this->getTextContent(new Environment(new \Twig\Loader\ArrayLoader())),
                    $transport
                );
            }
        };

        $email = $this->getFaker()->email();
        $phone = '+61412345678'; // Australian mobile number format

        $user = (new User())
            ->setEmail($email)
            ->setMobile($phone)
            ->setPassword('password');

        // Configure Twilio transport
        $transportSetting = (new TransportSetting())
            ->setName('Twilio SMS')
            ->setTransport('TwilioConfigurator')
            ->setUser($user)
            ->setSettings([
                'accountSid' => 'AC_TEST_SID',
                'authToken' => 'test_auth_token',
                'from' => '+61400000000', // Australian number
            ]);

        $userNotification = (new UserNotification())
            ->setEvent('test_sms_event')
            ->setEmail(false)
            ->setUser($user)
            ->addTransport($transportSetting);

        $em = static::getContainer()->get('doctrine.orm.entity_manager');
        $em->persist($user);
        $em->persist($transportSetting);
        $em->persist($userNotification);
        $em->flush();

        // Mock the Twilio configurator
        $configurator = M::mock(ConfiguratorInterface::class);
        $configurator
            ->expects('getType')
            ->once()
            ->andReturn('texter'); // 'texter' is the type for SMS

        $configurator
            ->expects('configure')
            ->with([
                'accountSid' => 'AC_TEST_SID',
                'authToken' => 'test_auth_token',
                'from' => '+61400000000',
            ])
            ->once()
            ->andReturn(new Dsn('twilio://AC_TEST_SID:test_auth_token@default?from=%2B61400000000'));

        // Expect the notification to be sent
        $this->notifier
            ->expects('send')
            ->with($class, IsEqual::equalTo(new Recipient($email, $phone)))
            ->once();

        $notificationManager = new NotificationManager(
            $this->notifier,
            static::getContainer()->get('doctrine')->getRepository(UserNotification::class),
            new ServiceLocator(['TwilioConfigurator' => static fn () => $configurator]),
        );

        $notificationManager->sendNotification($class);
        
        // Assert that SMS channel is included
        self::assertContains(
            'sms/' . $transportSetting->getId()->toString(), 
            $class->getChannels(new Recipient($email, $phone))
        );
    }

    public function testSmsContentFormatting(): void
    {
        $notification = new #[AsNotification(name: 'invoice_sms')] class extends NotificationMessage implements SmsNotificationInterface {
            public function getTextContent(Environment $twig): string
            {
                $params = $this->getParameters();
                return sprintf(
                    'Hi %s, Invoice #%s ($%s) is now %s. %s',
                    $params['clientName'] ?? 'Customer',
                    $params['invoiceNumber'] ?? 'INV-001',
                    $params['amount'] ?? '0.00',
                    $params['status'] ?? 'pending',
                    $params['message'] ?? ''
                );
            }

            public function asSmsMessage(SmsRecipientInterface $recipient, ?string $transport = null): SmsMessage
            {
                return new SmsMessage(
                    $recipient->getPhone(),
                    $this->getTextContent(new Environment(new \Twig\Loader\ArrayLoader())),
                    $transport
                );
            }
        };

        $notification->setParameters([
            'clientName' => 'John Smith',
            'invoiceNumber' => 'INV-2024-001',
            'amount' => '1,250.00',
            'status' => 'Paid',
            'message' => 'Thank you!'
        ]);

        $twig = new Environment(new \Twig\Loader\ArrayLoader());
        $content = $notification->getTextContent($twig);

        self::assertEquals(
            'Hi John Smith, Invoice #INV-2024-001 ($1,250.00) is now Paid. Thank you!',
            $content
        );
        self::assertLessThan(160, strlen($content), 'SMS should be under 160 characters');
    }
}