# Common Commands

This document lists frequently used commands for common development tasks in SolidInvoice.

All commands that interact with the PHP application (e.g., Symfony console commands, Composer) should be run inside the Docker container using `docker-compose exec app <command>`.

**Note:** The container name is `app` in the current docker-compose.yml configuration.

## Docker Commands

*   **Start all services:**
    ```bash
    docker-compose up -d
    ```
*   **Stop all services:**
    ```bash
    docker-compose down
    ```
*   **Build/Rebuild services:**
    ```bash
    docker-compose build
    ```
*   **View service logs:**
    ```bash
    docker-compose logs -f
    ```
*   **Execute a command in the app container:**
    ```bash
    docker-compose exec app bash
    # Then run your command inside the container
    ```

## Composer (PHP Dependency Management)

*   **Install PHP dependencies:**
    ```bash
    docker-compose exec app composer install
    ```
*   **Update PHP dependencies:**
    ```bash
    docker-compose exec app composer update
    ```
*   **Add a new dependency:**
    ```bash
    docker-compose exec app composer require vendor/package
    ```

## Symfony Console Commands (`bin/console`)

*   **Clear the Symfony cache:**
    ```bash
    docker-compose exec app bin/console cache:clear
    ```
*   **List all available commands:**
    ```bash
    docker-compose exec app bin/console list
    ```
*   **Run database migrations:**
    ```bash
    docker-compose exec app bin/console doctrine:migrations:migrate
    ```
*   **Generate a new migration:**
    ```bash
    docker-compose exec app bin/console doctrine:migrations:diff
    ```
*   **Load data fixtures:**
    ```bash
    docker-compose exec app bin/console doctrine:fixtures:load
    ```
*   **Debug routing:**
    ```bash
    docker-compose exec app bin/console debug:router
    ```
*   **Debug services:**
    ```bash
    docker-compose exec app bin/console debug:autowiring
    ```
*   **Check migration status:**
    ```bash
    docker-compose exec app bin/console doctrine:migrations:status
    ```
*   **Create a new bundle:**
    ```bash
    docker-compose exec app bin/console make:bundle
    ```

## Bun (JavaScript Dependency Management & Asset Building)

*   **Install JavaScript dependencies:**
    ```bash
    bun install
    ```
*   **Build frontend assets for production:**
    ```bash
    bun run build
    ```
*   **Run frontend asset watcher for development:**
    ```bash
    bun run watch
    ```

## Testing

*   **Run all PHPUnit tests:**
    ```bash
    docker-compose exec app bin/phpunit
    ```
*   **Run PHPUnit tests for a specific file/directory:**
    ```bash
    docker-compose exec app bin/phpunit tests/Unit/MyBundle/MyTest.php
    ```
*   **Run tests with coverage:**
    ```bash
    docker-compose exec app bin/phpunit --coverage-html coverage
    ```

## Code Quality & Linting

*   **Run PHPStan (static analysis):**
    ```bash
    docker-compose exec app vendor/bin/phpstan analyse
    ```
*   **Run ECS (EasyCodingStandard) to fix/check PHP code style:**
    ```bash
    docker-compose exec app vendor/bin/ecs check --fix
    # or check without fixing:
    # docker-compose exec app vendor/bin/ecs check
    ```
*   **Run ESLint (JavaScript/TypeScript linting):**
    ```bash
    bun run lint:js
    ```
*   **Run Stylelint (SCSS linting):**
    ```bash
    bun run lint:css
    ```

## SolidInvoice Specific Commands

*   **Test email configuration:**
    ```bash
    docker-compose exec app bin/console app:email:test your-email@example.com
    ```
*   **Test Brevo email specifically:**
    ```bash
    docker-compose exec app bin/console app:test-brevo-email your-email@example.com
    ```
*   **Send recurring invoices:**
    ```bash
    docker-compose exec app bin/console solidinvoice:recurring:send
    ```
*   **Install SolidInvoice:**
    ```bash
    docker-compose exec app bin/console solidinvoice:install
    ```
