# Email Configuration

SolidInvoice supports multiple email providers for sending invoices, quotes, and notifications. This guide explains how to configure email sending using various providers.

## Brevo API Configuration (Recommended)

Brevo (formerly Sendinblue) provides a reliable email API service. To configure Brevo API:

### 1. Get Your Brevo API Key

1. Sign up for a Brevo account at [https://www.brevo.com](https://www.brevo.com)
2. Go to your account settings and navigate to "SMTP & API"
3. Generate a new API key

### 2. Configure Environment Variables

Add your Brevo API key to your environment configuration:

```bash
# .env.local or .env
BREVO_API_KEY=your-brevo-api-key-here
```

### 3. Configure Mailer DSN

Set the mailer DSN to use Brevo API:

```bash
# .env.local or .env
SOLIDINVOICE_MAILER_DSN=brevo+api://${BREVO_API_KEY}@default
```

**Important**: Make sure to include `@default` at the end of the DSN!

### 4. Configure in Admin Panel

Alternatively, you can configure Brevo through the SolidInvoice admin panel:

1. Go to Settings → Email Configuration
2. Select "Brevo" as your mail provider
3. Enter your API key
4. Save the configuration

### 5. Test Your Configuration

Test your email configuration using the console command:

```bash
php bin/console app:email:test your-email@example.com
```

Or test specifically with Brevo API:

```bash
php bin/console app:test-brevo-email your-email@example.com
```

## Other Supported Email Providers

SolidInvoice also supports the following email providers:

- **SMTP**: Generic SMTP configuration
- **Gmail**: Google Gmail API
- **Sendgrid**: Sendgrid API
- **Mailgun**: Mailgun API
- **Postmark**: Postmark API
- **Amazon SES**: Amazon Simple Email Service
- **Mailchimp**: Mailchimp Transactional Email

## Environment Variables Reference

| Variable | Description | Example |
|----------|-------------|---------|
| `SOLIDINVOICE_MAILER_DSN` | Main mailer DSN configuration | `brevo+api://key@default` |
| `SOLIDINVOICE_MAILER_SENDER` | Default sender email address | `noreply@yourdomain.com` |
| `BREVO_API_KEY` | Brevo API key for authentication | `xkeysib-...` |

## Troubleshooting

### Common Issues

1. **Authentication Failed**: Verify your API key is correct and has the necessary permissions
2. **Rate Limiting**: Check your Brevo account limits and upgrade if necessary
3. **Invalid Sender**: Ensure the sender email is verified in your Brevo account
4. **"User is not set" Error**: This occurs when the DSN is malformed. Ensure your DSN includes `@default` at the end:
   - Wrong: `brevo+api://your-api-key`
   - Correct: `brevo+api://your-api-key@default`
5. **"Headers already sent" Error**: This is usually caused by the DSN parsing error above. Fix the DSN format first.

### Testing Commands

- Test general email configuration: `php bin/console app:email:test recipient@example.com`
- Test Brevo API specifically: `php bin/console app:test-brevo-email recipient@example.com`
- Use Symfony's built-in mailer test: `php bin/console mailer:test recipient@example.com`

### Logs

Check the application logs for detailed error messages:

```bash
tail -f var/log/dev.log
```

## Migration from SMTP to API

If you're currently using Brevo SMTP and want to switch to the API:

1. Update your `SOLIDINVOICE_MAILER_DSN` from `brevo+smtp://username:password@default` to `brevo+api://api-key@default`
2. Replace the username/password with your API key
3. Test the configuration
4. Update your settings in the admin panel if needed

The API method is generally more reliable and faster than SMTP.