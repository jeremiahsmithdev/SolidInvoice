# Development Setup

This document outlines the steps to set up the development environment for SolidInvoice.

## Prerequisites

Before you begin, ensure you have the following installed:

*   **PHP 8.3+:** SolidInvoice requires PHP version 8.3 or later for optimal performance
*   **Composer:** PHP dependency management tool
*   **Bun:** JavaScript package manager (version 1.2.10+) for managing frontend dependencies
*   **Git:** Version control system
*   **Docker Desktop:** (Optional) For containerized development environment

## Installation Options

SolidInvoice can be installed in several ways depending on your needs:

### Option 1: Docker (Recommended for Production)

Getting started with SolidInvoice is quick and simple using Docker. The Docker image can be found at [Docker Hub](https://hub.docker.com/r/solidinvoice/solidinvoice/).

### Option 2: Archived Package

Download the latest release in either `zip` or `tar.gz` format from [GitHub Releases](https://github.com/SolidInvoice/SolidInvoice/releases). Extract the contents into your web server directory.

### Option 3: Development Installation (Source Code)

For developers who want to contribute or customize SolidInvoice:

## Quick Start (Docker)

1.  **Clone the Repository:**

    ```bash
    git clone https://github.com/SolidInvoice/SolidInvoice.git
    cd SolidInvoice
    ```

2.  **Environment Configuration:**

    Copy the example environment file and configure it:

    ```bash
    cp .env.dist .env
    ```

    Edit `.env` to configure your settings:
    ```bash
    # Basic configuration
    SOLIDINVOICE_ENV=dev
    SOLIDINVOICE_DEBUG=1
    
    # Database (MySQL example)
    DATABASE_URL="mysql://solidinvoice:password@db:3306/solidinvoice?serverVersion=8.0"
    
    # Email configuration (optional for development)
    # MAILER_DSN=brevo+api://your-api-key@default
    ```

3.  **Start Docker Services:**

    Build and start all required services:

    ```bash
    docker-compose up -d
    ```

    This starts:
    - **MySQL 8.0** database server
    - **SolidInvoice application** with FrankenPHP server

4.  **Install Dependencies:**

    Install PHP dependencies:
    ```bash
    docker-compose exec app composer install
    ```

    Install JavaScript dependencies:
    ```bash
    bun install
    ```

5.  **Build Frontend Assets:**

    For development (with source maps):
    ```bash
    bun run dev
    ```

    For production (minified):
    ```bash
    bun run build
    ```

6.  **Database Setup:**

    Run migrations to create the database schema:
    ```bash
    docker-compose exec app bin/console doctrine:migrations:migrate --no-interaction
    ```

    Load sample data (optional):
    ```bash
    docker-compose exec app bin/console doctrine:fixtures:load --no-interaction
    ```

7.  **Access the Application:**

    Open your browser and navigate to:
    - **Application:** http://localhost:8765
    - **API Documentation:** http://localhost:8765/api/docs

## Development Workflow

### Asset Development

For active frontend development, run the watcher:
```bash
bun run watch
```

This will automatically recompile assets when files change.

### Database Changes

When modifying entities, generate migrations:
```bash
docker-compose exec app bin/console doctrine:migrations:diff
```

Apply migrations:
```bash
docker-compose exec app bin/console doctrine:migrations:migrate
```

### Code Quality

Run PHP static analysis:
```bash
docker-compose exec app vendor/bin/phpstan analyse
```

Fix PHP code style:
```bash
docker-compose exec app vendor/bin/ecs check --fix
```

Lint JavaScript/TypeScript:
```bash
bun run lint:js
```

Lint SCSS:
```bash
bun run lint:css
```

### Testing

Run PHP tests:
```bash
docker-compose exec app bin/phpunit
```

## Local Development Setup (Recommended for Development)

For development without Docker, follow the installation steps from the main README:

1. **Clone the repository:**
   ```bash
   git clone https://github.com/SolidInvoice/SolidInvoice.git
   cd SolidInvoice
   ```

2. **Install PHP dependencies:**
   ```bash
   # Get Composer if you don't have it
   curl -s http://getcomposer.org/installer | php
   
   # Install dependencies
   php composer.phar install
   # or if Composer is globally installed:
   composer install
   ```

3. **Install Node packages and compile assets:**
   ```bash
   bun install
   bun run dev
   ```

4. **Configure environment:**
   ```bash
   cp .env.dist .env
   # Edit .env with your database configuration
   DATABASE_URL="mysql://user:pass@localhost:3306/solidinvoice"
   ```

5. **Setup database:**
   ```bash
   php bin/console doctrine:migrations:migrate
   ```

6. **Start development server:**
   ```bash
   # Local access only
   php -S localhost:8000 -t public
   
   # Or for web access (if you have Symfony CLI)
   symfony serve --no-tls --allow-http --allow-all-ip
   
   # Or for network access
   php -S 0.0.0.0:8000 -t public
   ```

### Production Build

For production environments:

```bash
# Build optimized assets
bun run build

# Optimize Composer autoloader
composer install --no-dev --optimize-autoloader
```

## Troubleshooting

*   If you encounter issues, check the Docker logs:
    ```bash
    docker-compose logs
    ```
*   Ensure all services are running:
    ```bash
    docker-compose ps
    ```
