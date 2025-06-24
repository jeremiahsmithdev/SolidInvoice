<?php

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

#[AsCommand(name: 'app:email:test')]
class TestEmailCommand extends Command
{
    public function __construct(private MailerInterface $mailer)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $email = (new Email())
            ->from('admin@symbiotek.com.au') // Must match your verified sender
            ->to('yourpersonal@example.com')
            ->subject('Test Email from Symfony Mailer')
            ->text('This is a test email from SolidInvoice using Brevo.');

        $this->mailer->send($email);

        $output->writeln('✅ Test email sent.');
        return Command::SUCCESS;
    }
}
