# Database Management

This document outlines how the database is managed in SolidInvoice, focusing on Doctrine ORM, migrations, and database best practices.

## Doctrine ORM

SolidInvoice uses [Doctrine ORM](https://www.doctrine-project.org/projects/orm.html) for object-relational mapping. This allows interaction with the database using PHP objects rather than raw SQL queries.

### Entity Management

*   **Entities:** Database tables are mapped to PHP classes called Entities, typically found in `src/*/Entity/` directories (e.g., `src/InvoiceBundle/Entity/Invoice.php`).
*   **Repositories:** Custom repositories (e.g., `src/InvoiceBundle/Repository/InvoiceRepository.php`) are used to encapsulate database query logic for specific entities.

## Database Migrations

Database schema changes are managed using [Doctrine Migrations](https://www.doctrine-project.org/projects/migrations.html). Migrations are version-controlled PHP classes that contain the SQL necessary to upgrade or downgrade the database schema.

### Migration Files

Migration files are located in the `migrations/` directory and are named `VersionYYYYMMDDHHMMSS.php`.

### Common Migration Commands

All migration commands should be executed within the Docker container:

1.  **Generate a New Migration:**

    After making changes to your Doctrine Entities (e.g., adding a new field, creating a new entity), generate a new migration file:

    ```bash
    docker-compose exec app bin/console doctrine:migrations:diff
    ```

    This command compares the current database schema with the mapping information from your entities and generates a new migration file with the necessary SQL.

2.  **Execute Migrations (Apply Changes):**

    To apply pending migrations to the database:

    ```bash
    docker-compose exec app bin/console doctrine:migrations:migrate
    ```

    For automated deployment, use the `--no-interaction` flag:

    ```bash
    docker-compose exec app bin/console doctrine:migrations:migrate --no-interaction
    ```

3.  **Check Migration Status:**

    To see the status of your migrations (which ones have been executed and which are pending):

    ```bash
    docker-compose exec app bin/console doctrine:migrations:status
    ```

4.  **Rollback a Migration:**

    To revert the last executed migration:

    ```bash
    docker-compose exec app bin/console doctrine:migrations:rollback
    ```

    Use with caution, especially in production environments.

5.  **Execute a Specific Migration:**

    To migrate to a specific version:

    ```bash
    docker-compose exec app bin/console doctrine:migrations:execute --up VERSION
    ```

6.  **Dry Run (Preview SQL):**

    To see what SQL would be executed without actually running it:

    ```bash
    docker-compose exec app bin/console doctrine:migrations:migrate --dry-run
    ```

### Database Configuration

Database connection details are configured in the `.env` file, typically using the `DATABASE_URL` environment variable. The specific database type (MySQL, PostgreSQL) is determined by the DSN in this variable.

Example `.env` entry:

```dotenv
DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/db_name?serverVersion=8.0"
```

Or for PostgreSQL:

```dotenv
DATABASE_URL="postgresql://db_user:db_password@127.0.0.1:5432/db_name?serverVersion=13&charset=utf8"
```

## Entity Structure Overview

SolidInvoice uses a well-defined entity structure organized by business domains:

### Core Entities

- **Company** (`CoreBundle`): Multi-tenant company support
- **User** (`UserBundle`): Application users with role-based access
- **Client** (`ClientBundle`): Customer management with contacts and addresses
- **Invoice** (`InvoiceBundle`): Billing documents with line items
- **Quote** (`QuoteBundle`): Estimates that can be converted to invoices
- **Payment** (`PaymentBundle`): Payment records and gateway configurations

### Key Entity Relationships

```
Company (1) -> (many) Client
Client (1) -> (many) Contact
Client (1) -> (many) Address
Client (1) -> (many) Invoice
Client (1) -> (many) Quote
Invoice (1) -> (many) Line
Quote (1) -> (many) Line
Invoice (1) -> (many) Payment
```

### Entity Traits

SolidInvoice uses several traits for common functionality:

- **TimeStampable**: Automatic `created` and `updated` timestamps
- **CompanyAware**: Multi-tenant company association
- **Archivable**: Soft delete functionality

### Custom Doctrine Types

- **BigIntegerType**: For handling large monetary values
- **JsonArrayType**: For storing JSON data (legacy support)
- **UlidType**: For ULID primary keys

## Database Best Practices

### Entity Development

1. **Use ULID for Primary Keys**: New entities should use ULID instead of auto-increment integers
2. **Apply Appropriate Traits**: Use `TimeStampable`, `CompanyAware`, and `Archivable` where applicable
3. **Validate Relationships**: Ensure proper foreign key constraints and cascade options
4. **Index Strategy**: Add database indexes for frequently queried fields

### Migration Guidelines

1. **Always Review Generated Migrations**: Check the SQL before applying
2. **Test Migrations**: Run migrations on a copy of production data
3. **Backup Before Major Changes**: Always backup before schema modifications
4. **Use Descriptive Names**: Name migration files clearly when manually creating them

### Performance Considerations

1. **Lazy Loading**: Use lazy loading for relationships to avoid N+1 queries
2. **Query Optimization**: Use DQL/QueryBuilder for complex queries
3. **Pagination**: Implement pagination for large datasets using Pagerfanta
4. **Caching**: Leverage Doctrine's query and metadata caching