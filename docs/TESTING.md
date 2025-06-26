# Testing

This document outlines how to run tests for SolidInvoice and provides guidance on writing new tests.

## PHPUnit (Backend Tests)

SolidInvoice uses PHPUnit for unit and functional testing of the PHP backend, with Foundry for test data generation.

### Running All Tests

To run all PHPUnit tests, execute the following command from the project root:

```bash
docker-compose exec app bin/phpunit
```

### Running Specific Test Files or Directories

You can run tests for a specific file or directory by providing the path:

```bash
# Run tests in a specific file
docker-compose exec app bin/phpunit src/InvoiceBundle/Tests/Entity/InvoiceTest.php

# Run tests in a specific bundle
docker-compose exec app bin/phpunit src/InvoiceBundle/Tests/
```

### Running Tests with Filters

To run tests matching a specific name pattern:

```bash
docker-compose exec app bin/phpunit --filter "testCreateInvoice"
```

To run tests from a specific group:

```bash
docker-compose exec app bin/phpunit --group integration
```

### Test Coverage

Generate HTML coverage reports:

```bash
docker-compose exec app bin/phpunit --coverage-html coverage/
```

### Writing New PHPUnit Tests

#### Test Structure

Tests are organized within each bundle's `Tests/` directory:

```
src/
├── InvoiceBundle/
│   ├── Entity/
│   │   └── Invoice.php
│   └── Tests/
│       ├── Entity/
│       │   └── InvoiceTest.php
│       ├── Form/
│       └── Functional/
```

#### Test Types

1. **Unit Tests**: Test individual classes in isolation
2. **Integration Tests**: Test component interactions
3. **Functional Tests**: Test complete workflows using the web client

#### Using Foundry for Test Data

SolidInvoice uses Zenstruck Foundry for generating test data:

```php
use SolidInvoice\ClientBundle\Test\Factory\ClientFactory;
use SolidInvoice\InvoiceBundle\Test\Factory\InvoiceFactory;

// Create test data
$client = ClientFactory::createOne();
$invoice = InvoiceFactory::createOne(['client' => $client]);
```

#### Test Base Classes

- **FormTestCase**: For testing form types
- **ApiTestCase**: For testing API endpoints
- **WebTestCase**: For functional web tests

#### Example Test

```php
<?php

namespace SolidInvoice\InvoiceBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use SolidInvoice\InvoiceBundle\Entity\Invoice;
use SolidInvoice\InvoiceBundle\Test\Factory\InvoiceFactory;

class InvoiceTest extends TestCase
{
    public function testCreateInvoice(): void
    {
        $invoice = InvoiceFactory::createOne();
        
        $this->assertInstanceOf(Invoice::class, $invoice->object());
        $this->assertNotNull($invoice->getId());
    }
}
```

## Frontend Tests (JavaScript/TypeScript)

Currently, SolidInvoice focuses on backend testing with PHPUnit. Frontend testing is primarily done through:

1. **Asset Compilation Verification**: Ensuring TypeScript and SCSS compile without errors
2. **ESLint/Stylelint**: Code quality and style checking
3. **Manual Browser Testing**: Functional testing of UI components

### Asset Compilation Verification

After making changes to frontend assets, ensure they compile without errors:

```bash
bun run build
```

Any compilation errors will be reported in the console.

### Linting

Run JavaScript/TypeScript linting:

```bash
bun run lint:js
```

Run SCSS linting:

```bash
bun run lint:css
```

## Test Environment Setup

### Database Configuration

Tests use a separate test database configured in `.env.test`:

```bash
DATABASE_URL="mysql://test_user:test_pass@db:3306/solidinvoice_test?serverVersion=8.0"
```

### Running Tests in Isolation

Each test class should be independent and not rely on data from other tests. Use Foundry factories to create required test data.

### Fixtures vs Factories

- **Fixtures**: Use for loading static reference data (e.g., tax rates, payment methods)
- **Factories**: Use for creating dynamic test data (e.g., clients, invoices, users)

## Continuous Integration

SolidInvoice uses GitHub Actions for CI/CD with the following test workflows:

1. **Unit Tests**: PHPUnit test suite
2. **Static Analysis**: PHPStan analysis
3. **Code Style**: ECS (EasyCodingStandard) checks
4. **Security**: Security checker for dependencies
5. **Database Tests**: Tests against multiple database versions

## Best Practices

### Test Naming

- Test classes: `{ClassName}Test.php`
- Test methods: `test{MethodName}()` or use `@test` annotation
- Use descriptive names that explain what is being tested

### Test Organization

- Group related tests in the same class
- Use `setUp()` and `tearDown()` methods for common test preparation
- Keep tests focused and test one thing at a time

### Assertions

- Use specific assertions (`assertSame` vs `assertEquals`)
- Include meaningful failure messages
- Test both positive and negative cases

### Data Providers

Use data providers for testing multiple scenarios:

```php
/**
 * @dataProvider invoiceStatusProvider
 */
public function testInvoiceStatusTransition(string $from, string $to, bool $expected): void
{
    // Test implementation
}

public function invoiceStatusProvider(): array
{
    return [
        ['draft', 'pending', true],
        ['pending', 'paid', true],
        ['paid', 'draft', false],
    ];
}
```