# SolidInvoice Documentation

Welcome to the comprehensive documentation for SolidInvoice - a sophisticated open-source invoicing application designed to assist small businesses and freelancers in efficiently managing their daily billing operations.

## 📚 Documentation Index

### Getting Started
- **[Project Overview](PROJECT_OVERVIEW.md)** - Complete overview of SolidInvoice architecture and features
- **[Development Setup](DEVELOPMENT_SETUP.md)** - Installation and setup instructions for development
- **[System Requirements](#system-requirements)** - PHP 8.3+ and other requirements

### Development Guides
- **[Architecture](ARCHITECTURE.md)** - Comprehensive system architecture documentation
- **[Database Management](DATABASE.md)** - Doctrine ORM, migrations, and database best practices
- **[Frontend Development](FRONTEND_DEVELOPMENT.md)** - Stimulus, TypeScript, SCSS development guide
- **[API Development](API_DEVELOPMENT.md)** - REST API development with API Platform
- **[Code Conventions](CODE_CONVENTIONS.md)** - Coding standards and best practices
- **[Testing](TESTING.md)** - Testing strategies and guidelines

### Operations
- **[Build & Deployment](BUILD_DEPLOY.md)** - Asset compilation, Docker builds, and deployment
- **[Common Commands](COMMON_COMMANDS.md)** - Frequently used development commands
- **[Email Configuration](EMAIL_CONFIGURATION.md)** - Email provider setup and configuration

### Project Management
- **[Project Scope](SCOPE.md)** - Tree felling business specialization scope and roadmap

## 🚀 Quick Start

### For Users
1. **Docker Installation**: Use the official Docker image from [Docker Hub](https://hub.docker.com/r/solidinvoice/solidinvoice/)
2. **Archived Package**: Download from [GitHub Releases](https://github.com/SolidInvoice/SolidInvoice/releases)

### For Developers
1. **Clone Repository**: `git clone https://github.com/SolidInvoice/SolidInvoice.git`
2. **Install Dependencies**: `composer install && bun install`
3. **Setup Environment**: Copy `.env.dist` to `.env` and configure
4. **Build Assets**: `bun run dev`
5. **Start Server**: `php -S localhost:8000 -t public`

## 🏗️ System Requirements

- **PHP 8.3+** (latest version recommended)
- **MySQL 8.0+** or **PostgreSQL 13+**
- **Composer** for PHP dependency management
- **Bun** for JavaScript package management
- **Docker** (optional, for containerized deployment)

## 🎯 Key Features

- **Robust Client Management**: Comprehensive clients and contacts management
- **Quote & Invoice Management**: Professional quote and invoice generation
- **Payment Processing**: Multiple payment gateways via Payum integration
- **Tax & Discount Handling**: Flexible tax calculation and discount management
- **RESTful API**: Complete API for third-party integrations
- **Multi-Channel Notifications**: Email, SMS, and chat notifications
- **Multi-Tenant Support**: Company-based data isolation

## 🔧 Architecture Overview

SolidInvoice follows a **Domain-Driven Design (DDD)** approach using Symfony's bundle system:

### Core Business Bundles
- **ClientBundle**: Customer and contact management
- **InvoiceBundle**: Invoice creation and management
- **QuoteBundle**: Quote generation and conversion
- **PaymentBundle**: Payment processing and gateways
- **UserBundle**: Authentication and user management

### Infrastructure Bundles
- **CoreBundle**: Shared functionality and utilities
- **ApiBundle**: REST API endpoints and authentication
- **MailerBundle**: Email configuration and sending
- **NotificationBundle**: Multi-channel notifications
- **SettingsBundle**: Application configuration

## 🛠️ Technology Stack

### Backend
- **PHP 8.3+** with **Symfony 7.x**
- **Doctrine ORM** for database management
- **API Platform** for REST API generation
- **Payum** for payment gateway integration
- **FrankenPHP** for high-performance serving

### Frontend
- **TypeScript** for type-safe JavaScript
- **Stimulus** for progressive enhancement
- **SCSS** with Bootstrap 4 base
- **Webpack Encore** for asset compilation
- **Twig** for server-side templating

### Infrastructure
- **MySQL 8.0+** or **PostgreSQL** database
- **Docker** for containerization
- **Bun** for JavaScript package management

## 📈 Recent Enhancements

### Tree Felling Business Specialization
- ✅ **Bank Transfer Integration**: Automated bank details on invoices/quotes
- ✅ **Australian Banking Support**: BSB and account number validation
- ✅ **Payment Template System**: Configurable payment method displays
- ✅ **Enhanced Workflow**: Streamlined quote-to-invoice process

## 🤝 Contributing

We welcome contributions! Please see:
- **[Contributing Guidelines](../CONTRIBUTING.md)** - How to contribute to the project
- **[Code Conventions](CODE_CONVENTIONS.md)** - Coding standards and practices
- **[Testing Guidelines](TESTING.md)** - Testing requirements and strategies

## 📄 License

SolidInvoice is licensed under the **MIT License** - see the [LICENSE](../LICENSE) file for details.

## 🙏 Acknowledgments

Special thanks to our sponsors:
- **JetBrains** (PHPStorm License)
- **Docker** (Docker Hub Subscription)
- **Sentry** (Sponsored Business plan)

## 📞 Support

- **GitHub Issues**: [Report bugs or request features](https://github.com/SolidInvoice/SolidInvoice/issues)
- **Documentation**: This comprehensive documentation set
- **Community**: Join our community discussions

---

*This documentation is maintained alongside the codebase to ensure accuracy and completeness. Last updated: 2024*