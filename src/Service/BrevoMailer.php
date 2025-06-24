<?php

namespace SolidInvoice\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;

class BrevoMailer
{
    public function __construct(
        private HttpClientInterface $client,
        private string $brevoApiKey
    ) {}

    public function sendTestEmail(string $to): bool
    {
        $response = $this->client->request('POST', 'https://api.brevo.com/v3/smtp/email', [
            'headers' => [
                'api-key' => $this->brevoApiKey,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'sender' => ['email' => 'admin@symbiotek.com.au', 'name' => 'Symbiotek'],
                'to' => [['email' => $to]],
                'subject' => 'Test from Brevo API',
                'htmlContent' => '<p>This is a test email sent using Brevo API.</p>',
            ],
        ]);

        return $response->getStatusCode() === 201;
    }
}
