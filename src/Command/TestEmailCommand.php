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

namespace SolidInvoice\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

#[AsCommand(name: 'app:email:test', description: 'Test sending email via configured mailer')]
class TestEmailCommand extends Command
{
    public function __construct(private MailerInterface $mailer)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('to', InputArgument::REQUIRED, 'Recipient email address')
            ->addOption('from', 'f', InputOption::VALUE_OPTIONAL, 'Sender email address', 'admin@symbiotek.com.au')
            ->addOption('subject', 's', InputOption::VALUE_OPTIONAL, 'Email subject', 'Test Email from SolidInvoice')
            ->setHelp('This command sends a test email via the configured mailer to verify the integration works.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $to = $input->getArgument('to');
        $from = $input->getOption('from');
        $subject = $input->getOption('subject');

        $io->info('Sending test email via configured mailer...');

        try {
            $email = (new Email())
                ->from($from)
                ->to($to)
                ->subject($subject)
                ->text('This is a test email from SolidInvoice using the configured mailer.')
                ->html('<p>This is a test email from SolidInvoice using the configured mailer.</p>');

            $this->mailer->send($email);

            $io->success(sprintf('Test email successfully sent to %s!', $to));
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error(sprintf('Error sending email: %s', $e->getMessage()));
            return Command::FAILURE;
        }
    }
}
