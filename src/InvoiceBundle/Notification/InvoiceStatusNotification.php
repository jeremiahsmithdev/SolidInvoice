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

namespace SolidInvoice\InvoiceBundle\Notification;

use SolidInvoice\NotificationBundle\Attribute\AsNotification;
use SolidInvoice\NotificationBundle\Notification\NotificationMessage;
use Symfony\Bridge\Twig\Mime\NotificationEmail;
use Symfony\Component\Notifier\Message\EmailMessage;
use Symfony\Component\Notifier\Message\SmsMessage;
use Symfony\Component\Notifier\Notification\SmsNotificationInterface;
use Symfony\Component\Notifier\Recipient\EmailRecipientInterface;
use Symfony\Component\Notifier\Recipient\SmsRecipientInterface;
use Twig\Environment;

#[AsNotification(name: self::EVENT)]
class InvoiceStatusNotification extends NotificationMessage implements SmsNotificationInterface
{
    public const EVENT = 'invoice_status_update';

    final public const HTML_TEMPLATE = '@SolidInvoiceInvoice/Email/status_change.html.twig';

    final public const TEXT_TEMPLATE = '@SolidInvoiceInvoice/Email/status_change.text.twig';

    public function getTextContent(Environment $twig): string
    {
        return $twig->render(self::TEXT_TEMPLATE, $this->getParameters());
    }

    public function getSubject(): string
    {
        return 'Invoice Status Change';
    }

    public function asEmailMessage(EmailRecipientInterface $recipient, ?string $transport = null): EmailMessage
    {
        $message = parent::asEmailMessage($recipient, $transport);

        $email = $message->getMessage();

        if ($email instanceof NotificationEmail) {
            $email->textTemplate(self::TEXT_TEMPLATE);
            $email->htmlTemplate(self::HTML_TEMPLATE);
            $email->context($this->getParameters());
        }

        return $message;
    }

    public function asSmsMessage(SmsRecipientInterface $recipient, ?string $transport = null): SmsMessage
    {
        $params = $this->getParameters();
        
        // Format a concise SMS message (max 160 chars)
        $smsText = sprintf(
            'Invoice #%s (%s) status: %s. Amount: %s',
            $params['invoice']['number'] ?? 'N/A',
            $params['invoice']['client']['name'] ?? 'Customer',
            $params['newStatus'] ?? 'Updated',
            $params['invoice']['total']['amount'] ?? '0.00'
        );
        
        // Truncate if too long
        if (strlen($smsText) > 160) {
            $smsText = substr($smsText, 0, 157) . '...';
        }
        
        return new SmsMessage($recipient->getPhone(), $smsText, $transport);
    }
}
