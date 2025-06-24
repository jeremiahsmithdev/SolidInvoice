<?php

declare(strict_types=1);

namespace SolidInvoice\Command;

use SolidInvoice\Service\BrevoMailer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: 'app:test-brevo-email', description: 'Test sending email via Brevo API')]
class TestBrevoEmailCommand extends Command
{
    public function __construct(
        private readonly BrevoMailer $brevoMailer
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Recipient email address')
            ->setHelp('This command sends a test email via Brevo API to verify the integration works.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');

        $io->info('Sending test email via Brevo API...');

        try {
            $success = $this->brevoMailer->sendTestEmail($email);

            if ($success) {
                $io->success(sprintf('Test email successfully sent to %s via Brevo API!', $email));
                return Command::SUCCESS;
            } else {
                $io->error('Failed to send email via Brevo API');
                return Command::FAILURE;
            }
        } catch (\Exception $e) {
            $io->error(sprintf('Error sending email: %s', $e->getMessage()));
            return Command::FAILURE;
        }
    }
}