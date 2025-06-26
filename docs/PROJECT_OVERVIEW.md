# Project Overview: SolidInvoice

SolidInvoice is a sophisticated open-source invoicing application designed to assist small businesses and freelancers in efficiently managing their daily billing operations. With its comprehensive range of features, this elegant online platform ensures that you receive timely payments.

The application has been enhanced with specialized features for service-based businesses, particularly tree felling and landscaping operations, while maintaining its core functionality for general business use.

## System Requirements

SolidInvoice requires **PHP version 8.3 or later** for optimal performance. It is recommended to use the latest available version of PHP.

Additional requirements:
- **MySQL 8.0+** or **PostgreSQL 13+** for database
- **Composer** for PHP dependency management
- **Bun** for JavaScript package management
- **Docker** (optional, for containerized deployment)

## Core Technologies

*   **Backend:** PHP 8.3+ with Symfony Framework 7.x
*   **Frontend:** TypeScript/JavaScript with Symfony UX (Stimulus), SCSS, Bootstrap 4, AdminLTE theme
*   **Build Tools:** Webpack Encore for asset compilation, Bun for JavaScript package management
*   **Database:** MySQL 8.0+ or PostgreSQL (via Doctrine ORM)
*   **Application Server:** FrankenPHP (Caddy-based) for high-performance PHP serving
*   **API:** API Platform for RESTful API endpoints
*   **Payments:** Payum integration supporting multiple payment gateways
*   **Containerization:** Docker and Docker Compose for development and deployment

## Architecture

SolidInvoice follows a **Domain-Driven Design (DDD)** approach using Symfony's bundle system. The application is organized into 15+ specialized bundles, each handling a specific business domain:

### Core Business Bundles
- **ClientBundle**: Customer/client management with contacts and addresses
- **InvoiceBundle**: Invoice creation, management, and recurring invoices
- **QuoteBundle**: Quote/estimate generation and conversion to invoices
- **PaymentBundle**: Payment processing with multiple gateway support
- **TaxBundle**: Tax calculation and management
- **UserBundle**: User authentication, authorization, and API tokens

### Infrastructure Bundles
- **CoreBundle**: Shared utilities, base entities, and common functionality
- **ApiBundle**: REST API endpoints and authentication
- **MailerBundle**: Email configuration and sending (Brevo, SMTP, etc.)
- **NotificationBundle**: Multi-channel notifications (email, SMS, chat)
- **DataGridBundle**: Reusable data grid components
- **MenuBundle**: Dynamic menu system
- **SettingsBundle**: Application configuration management

### Specialized Features
- **CronBundle**: Scheduled task management
- **DashboardBundle**: Business intelligence widgets
- **InstallBundle**: Application setup and installation wizard
- **SaasBundle**: Multi-tenant SaaS functionality (optional)

## Key Domain Models

### Client Management
- **Client**: Primary customer entity with billing information
- **Contact**: Individual contacts within client organizations
- **Address**: Billing and service addresses
- **Credit**: Client credit balances and adjustments

### Billing & Invoicing
- **Invoice**: Billable documents with line items and payment tracking
- **Quote**: Estimates that can be converted to invoices
- **RecurringInvoice**: Automated recurring billing
- **Line**: Individual line items for services/products
- **Payment**: Payment records linked to invoices

### Business Logic
- **Company**: Multi-company support with data isolation
- **Tax**: Configurable tax rates and calculations
- **Discount**: Percentage and fixed-amount discounts
- **PaymentMethod**: Configurable payment gateway settings

## Frontend Architecture

The frontend uses a **progressive enhancement** approach:

- **Stimulus Controllers**: Interactive JavaScript components
- **Twig Components**: Server-side rendered UI components
- **Live Components**: Real-time updates without full page reloads
- **SCSS Modules**: Modular styling with Bootstrap 4 base
- **TypeScript**: Type-safe JavaScript development

## Key Features

- **Robust Client Management**: Comprehensive clients and contacts management system
- **Quote Management**: Creation and management of quotes with conversion to invoices
- **Invoice Generation**: Professional invoice generation and oversight
- **Payment Processing**: Seamless online payment acceptance through multiple gateways
- **Tax & Discount Handling**: Effective handling of taxes and discounts
- **RESTful API**: Complete API for integration with other systems
- **Multi-Channel Notifications**: Receive notifications through various channels (email, SMS, chat)
- **Multi-Tenant Support**: Company-based data isolation for multiple businesses

## Recent Enhancements

### Tree Felling Business Specialization
- **Bank Transfer Integration**: Automated bank details on invoices/quotes
- **Australian Banking Support**: BSB and account number validation
- **Payment Template System**: Configurable payment method displays
- **Enhanced Quote-to-Invoice Workflow**: Streamlined business process

## Installation Options

SolidInvoice offers multiple installation methods:

### Docker (Recommended)
Quick and simple setup using the official Docker image available at [Docker Hub](https://hub.docker.com/r/solidinvoice/solidinvoice/).

### Archived Package
Download the latest release in `zip` or `tar.gz` format from [GitHub Releases](https://github.com/SolidInvoice/SolidInvoice/releases) and extract to your web server directory.

### Source Code Installation
Clone the repository and install dependencies for development or customization:
```bash
git clone https://github.com/SolidInvoice/SolidInvoice.git
composer install
bun install && bun run dev
```

## Data Flow

1. **Client Onboarding**: Create client with contacts and service addresses
2. **Quote Generation**: Create estimates with line items and terms
3. **Quote Approval**: Convert approved quotes to invoices
4. **Payment Processing**: Handle payments through configured gateways
5. **Recurring Billing**: Automated invoice generation for ongoing services
6. **Reporting**: Dashboard analytics and financial reporting

## License & Contributing

SolidInvoice is licensed under the **MIT License** - an open-source software license that allows for free use, modification, and distribution.

For contribution guidelines, please refer to the [CONTRIBUTING](../CONTRIBUTING.md) file.
