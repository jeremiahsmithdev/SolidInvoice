# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

SolidInvoice is an open-source invoicing application built with Symfony 7.1 and specialized for tree felling and landscaping businesses. It uses a Domain-Driven Design approach with a modular monolith architecture.

## Tech Stack

- **Backend**: PHP 8.3+, Symfony 7.1, Doctrine ORM, API Platform 4.0
- **Frontend**: TypeScript, Stimulus (Symfony UX), Twig with Live Components, Bootstrap 4/AdminLTE
- **Database**: MySQL 8.0+ or PostgreSQL 13+, uses ULID for primary keys
- **Build Tools**: Bun (v1.2.10), Webpack Encore
- **Development**: Docker Compose, FrankenPHP (Caddy-based)

## Essential Commands

### Development Setup
```bash
# Start services
docker-compose up -d

# Install dependencies
docker-compose exec app composer install
bun install

# Watch frontend assets
bun run watch

# Access at http://localhost:8000 or http://localhost:3000
# Test login: jeremiah@symbiotek.com.au / Thr3ftygui!
```

### Testing
```bash
# Run all PHP tests
docker-compose exec app bin/phpunit

# Run specific test
docker-compose exec app bin/phpunit src/InvoiceBundle/Tests/Entity/InvoiceTest.php

# Code quality checks
docker-compose exec app vendor/bin/phpstan analyse
docker-compose exec app vendor/bin/ecs check --fix
bun run lint:js
bun run lint:css
```

### Database Operations
```bash
# Run migrations
docker-compose exec app bin/console doctrine:migrations:migrate

# Generate migration from entity changes
docker-compose exec app bin/console doctrine:migrations:diff

# Load fixtures
docker-compose exec app bin/console doctrine:fixtures:load
```

### Build & Deployment
```bash
# Production build
bun run build

# Clear cache
docker-compose exec app bin/console cache:clear
```

## Architecture & Code Organization

The application is organized into 15+ Symfony bundles, each representing a business domain:

- **Core Business**: ClientBundle, InvoiceBundle, QuoteBundle, PaymentBundle, TaxBundle, MapBundle
- **Infrastructure**: CoreBundle, ApiBundle, UserBundle, SettingsBundle, NotificationBundle
- **Support**: InstallBundle, MigrationBundle, MoneyBundle

### Key Patterns
- **Repository Pattern**: Data access layer (e.g., `ClientRepository`)
- **Form Handler Pattern**: Business logic encapsulation
- **Event-Driven Architecture**: Domain events for decoupling
- **Factory Pattern**: Test data generation with Foundry
- **Multi-Tenancy**: Company-based data isolation

### File Structure Example
**NOTE**: Read the /docs/NAVIGATION.md file for a more in-depth explanation of how to navigate the codebase
```
src/
├── ClientBundle/
│   ├── Entity/          # Domain entities
│   ├── Repository/      # Data access
│   ├── Form/           # Form types and handlers
│   ├── Controller/     # HTTP endpoints
│   └── Tests/          # PHPUnit tests
```

## Development Guidelines

- Before significant features or refactoring, check the [SolidInvoice](https://github.com/lucazulian/SolidInvoice) documentation
- Review docs in `/docs` directory for architecture and implementation details
- Follow existing code conventions - check neighboring files for patterns
- Use existing libraries/utilities rather than adding new dependencies
- Test changes at http://localhost:3000 with test credentials
- Monitor for errors in:
  - Symfony logs: `var/log/dev.log`
  - Browser console for JavaScript errors
  - Stack traces shown in browser on crashes

## Recent Enhancements

- Client contact integration (merged contact info into client form)
- Interactive map visualization with Leaflet.js
- Simplified invoice/quote creation workflow
- Australian banking support (BSB/account validation)

## Important Notes

- XDebug is not currently configured
- Use Docker commands for all PHP operations (prefix with `docker-compose exec app`)
- Update `/docs` documentation when making architectural changes
- Ensure all tests pass before completing tasks
- Multi-company support requires company context in all queries
