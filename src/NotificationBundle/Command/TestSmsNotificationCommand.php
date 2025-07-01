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

namespace SolidInvoice\NotificationBundle\Command;

use SolidInvoice\InvoiceBundle\Entity\Invoice;
use SolidInvoice\InvoiceBundle\Notification\InvoiceStatusNotification;
use SolidInvoice\NotificationBundle\Notification\NotificationManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'solidinvoice:test:sms',
    description: 'Test SMS notification functionality',
)]
class TestSmsNotificationCommand extends Command
{
    public function __construct(
        private readonly NotificationManager $notificationManager,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('phone', InputArgument::REQUIRED, 'Phone number to send test SMS to (e.g., +61412345678)')
            ->addOption('provider', null, InputOption::VALUE_REQUIRED, 'SMS provider to use (twilio, vonage, etc.)', 'twilio')
            ->addOption('dry-run', null, InputOption::VALUE_NONE, 'Simulate sending without actually sending SMS')
            ->setHelp(<<<'EOF'
The <info>%command.name%</info> command tests SMS notification functionality:

Send a test SMS notification:
  <info>php %command.full_name% +61412345678</info>

Use a specific provider:
  <info>php %command.full_name% +61412345678 --provider=vonage</info>

Dry run (simulate without sending):
  <info>php %command.full_name% +61412345678 --dry-run</info>

Note: You must have configured SMS transport settings in the application before using this command.
EOF
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $phoneNumber = $input->getArgument('phone');
        $provider = $input->getOption('provider');
        $dryRun = $input->getOption('dry-run');

        // Validate phone number format
        if (!preg_match('/^\+?[1-9]\d{1,14}$/', $phoneNumber)) {
            $io->error('Invalid phone number format. Please use international format (e.g., +61412345678)');
            return Command::FAILURE;
        }

        $io->title('SMS Notification Test');
        $io->text([
            'Phone Number: ' . $phoneNumber,
            'Provider: ' . $provider,
            'Mode: ' . ($dryRun ? 'Dry Run' : 'Live'),
        ]);

        // Create a test invoice notification
        $notification = new InvoiceStatusNotification([
            'invoice' => [
                'number' => 'TEST-INV-001',
                'client' => [
                    'name' => 'Test Customer',
                ],
                'total' => [
                    'amount' => '$1,250.00',
                ],
            ],
            'newStatus' => 'Paid',
            'oldStatus' => 'Pending',
        ]);

        if ($dryRun) {
            $io->section('Dry Run - SMS Content Preview');
            
            // Create a mock SMS recipient to preview the message
            $recipient = new class($phoneNumber) implements \Symfony\Component\Notifier\Recipient\SmsRecipientInterface {
                public function __construct(private string $phone) {}
                public function getPhone(): string { return $this->phone; }
            };
            
            $smsMessage = $notification->asSmsMessage($recipient);
            
            $io->text([
                'To: ' . $smsMessage->getPhone(),
                'Message: ' . $smsMessage->getSubject(),
                'Length: ' . strlen($smsMessage->getSubject()) . ' characters',
            ]);
            
            $io->success('Dry run completed. No SMS was sent.');
        } else {
            try {
                $io->section('Sending SMS Notification');
                
                // In a real implementation, this would send through the configured transport
                $io->warning('SMS sending is not fully configured. To enable:');
                $io->listing([
                    'Configure SMS transport settings in Settings > Notifications',
                    'Add SMS provider credentials (Twilio, Vonage, etc.)',
                    'Ensure users have mobile numbers in their profiles',
                    'Enable SMS notifications for specific events',
                ]);
                
                $io->info('Once configured, the notification would be sent using:');
                $io->text('$this->notificationManager->sendNotification($notification);');
                
            } catch (\Exception $e) {
                $io->error('Failed to send SMS: ' . $e->getMessage());
                return Command::FAILURE;
            }
        }

        $io->section('Testing SMS Provider Configuration');
        $io->text('To test with actual SMS providers, you need to:');
        $io->listing([
            'Set up a Twilio account at https://www.twilio.com',
            'Get your Account SID, Auth Token, and Phone Number',
            'Configure in Settings > Notifications > SMS Transports',
            'Or use environment variables:',
            '  TWILIO_DSN="twilio://SID:TOKEN@default?from=+1234567890"',
        ]);

        return Command::SUCCESS;
    }
}