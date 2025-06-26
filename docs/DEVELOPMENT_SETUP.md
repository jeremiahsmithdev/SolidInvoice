# Development Setup

This document outlines the steps to set up the development environment for SolidInvoice.

## Prerequisites

Before you begin, ensure you have the following installed:

*   **Docker Desktop:** (or Docker Engine and Docker Compose) for running the application services
*   **Bun:** JavaScript package manager (version 1.2.10+) for managing frontend dependencies
*   **Git:** Version control system

## Quick Start

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

## Alternative Setup (Local Development)

If you prefer to run PHP locally instead of Docker:

1. **Requirements:**
   - PHP 8.3+
   - MySQL 8.0+ or PostgreSQL 13+
   - Composer

2. **Setup:**
   ```bash
   composer install
   bun install
   bun run build
   
   # Configure .env with local database
   DATABASE_URL="mysql://user:pass@localhost:3306/solidinvoice"
   
   # Run migrations
   php bin/console doctrine:migrations:migrate
   
   # Start development server
   symfony server:start
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
