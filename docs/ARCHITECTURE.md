# SolidInvoice Architecture

This document provides a comprehensive overview of SolidInvoice's architecture, design patterns, and technical decisions.

## High-Level Architecture

SolidInvoice follows a **Domain-Driven Design (DDD)** approach with a modular monolith architecture using Symfony bundles.

```
┌─────────────────────────────────────────────────────────────┐
│                    Presentation Layer                       │
├─────────────────────────────────────────────────────────────┤
│  Web UI (Twig)  │  API (API Platform)  │  CLI (Console)    │
├─────────────────────────────────────────────────────────────┤
│                    Application Layer                        │
├─────────────────────────────────────────────────────────────┤
│  Controllers  │  Form Handlers  │  Commands  │  Listeners   │
├─────────────────────────────────────────────────────────────┤
│                     Domain Layer                            │
├─────────────────────────────────────────────────────────────┤
│  Entities  │  Repositories  │  Services  │  Value Objects   │
├─────────────────────────────────────────────────────────────┤
│                  Infrastructure Layer                       │
├─────────────────────────────────────────────────────────────┤
│  Database  │  Email  │  Payments  │  File System  │  Cache  │
└─────────────────────────────────────────────────────────────┘
```

## Bundle Architecture

### Core Business Bundles

#### ClientBundle
**Purpose**: Customer and contact management
- **Entities**: Client, Contact, Address, Credit
- **Key Features**: Multi-contact support, address management, credit tracking
- **Dependencies**: CoreBundle, UserBundle

#### InvoiceBundle  
**Purpose**: Invoice creation and management
- **Entities**: Invoice, Line, RecurringInvoice, RecurringOptions
- **Key Features**: Line items, recurring billing, PDF generation, email sending
- **Dependencies**: ClientBundle, PaymentBundle, TaxBundle

#### QuoteBundle
**Purpose**: Quote/estimate generation
- **Entities**: Quote, Line
- **Key Features**: Quote-to-invoice conversion, PDF generation, approval workflow
- **Dependencies**: ClientBundle, InvoiceBundle

#### PaymentBundle
**Purpose**: Payment processing and gateway management
- **Entities**: Payment, PaymentMethod, SecurityToken
- **Key Features**: Multiple payment gateways (Payum), payment tracking
- **Dependencies**: InvoiceBundle, CoreBundle

### Infrastructure Bundles

#### CoreBundle
**Purpose**: Shared functionality and base components
- **Entities**: Company, Discount, Version
- **Key Features**: Multi-tenancy, common traits, utilities
- **Dependencies**: None (foundational)

#### ApiBundle
**Purpose**: REST API endpoints
- **Features**: API Platform integration, authentication, serialization
- **Dependencies**: All business bundles

#### MailerBundle
**Purpose**: Email configuration and sending
- **Features**: Multiple providers (Brevo, SMTP, etc.), template management
- **Dependencies**: CoreBundle

### Supporting Bundles

#### UserBundle
**Purpose**: Authentication and user management
- **Entities**: User, ApiToken, UserInvitation
- **Key Features**: OAuth integration, API tokens, user invitations

#### SettingsBundle
**Purpose**: Application configuration
- **Features**: Dynamic settings, form-based configuration

#### NotificationBundle
**Purpose**: Multi-channel notifications
- **Features**: Email, SMS, chat notifications

## Design Patterns

### Repository Pattern
Each entity has a corresponding repository for data access:

```php
// Entity
class Invoice
{
    // Entity properties and methods
}

// Repository
class InvoiceRepository extends ServiceEntityRepository
{
    public function findByClient(Client $client): array
    {
        return $this->createQueryBuilder('i')
            ->where('i.client = :client')
            ->setParameter('client', $client)
            ->getQuery()
            ->getResult();
    }
}
```

### Form Handler Pattern
Business logic for form processing is encapsulated in form handlers:

```php
class InvoiceCreateHandler extends AbstractFormHandler
{
    public function handle(FormRequest $formRequest): bool
    {
        // Handle form submission and business logic
    }
}
```

### Event-Driven Architecture
Domain events are used for decoupling:

```php
// Event
class InvoicePaidEvent extends Event
{
    public function __construct(private Invoice $invoice) {}
}

// Listener
class InvoicePaidListener
{
    public function onInvoicePaid(InvoicePaidEvent $event): void
    {
        // Handle invoice paid logic
    }
}
```

### Factory Pattern
Test data generation uses the Factory pattern with Foundry:

```php
class InvoiceFactory extends ModelFactory
{
    protected function getDefaults(): array
    {
        return [
            'client' => ClientFactory::new(),
            'status' => 'draft',
            'total' => Money::USD(10000),
        ];
    }
}
```

## Data Flow

### Invoice Creation Workflow

```
1. User creates invoice via form
2. InvoiceCreateHandler processes form
3. Invoice entity is persisted
4. InvoiceCreatedEvent is dispatched
5. Listeners handle side effects (email, notifications)
6. Response is returned to user
```

### Payment Processing Workflow

```
1. Customer initiates payment
2. PaymentBundle routes to appropriate gateway
3. Payum handles payment processing
4. Payment entity is created
5. PaymentCompleteEvent is dispatched
6. Invoice status is updated
7. Notifications are sent
```

## Security Architecture

### Authentication
- **Session-based**: For web interface
- **API Token**: For API access
- **OAuth**: For third-party integrations

### Authorization
- **Role-based**: User roles determine access
- **Company-based**: Multi-tenant data isolation
- **Resource-based**: Entity-level permissions

### Data Protection
- **Encryption**: Sensitive data encryption at rest
- **Validation**: Input validation and sanitization
- **CSRF Protection**: Form token validation
- **SQL Injection Prevention**: Doctrine ORM parameterized queries

## Performance Considerations

### Database Optimization
- **Indexing**: Strategic database indexes
- **Query Optimization**: Efficient DQL/SQL queries
- **Lazy Loading**: Doctrine lazy loading for relationships
- **Pagination**: Pagerfanta for large datasets

### Caching Strategy
- **OPcache**: PHP bytecode caching
- **Doctrine Cache**: Query and metadata caching
- **HTTP Cache**: Response caching with Symfony
- **Asset Caching**: Versioned static assets

### Frontend Performance
- **Asset Bundling**: Webpack Encore optimization
- **Code Splitting**: Separate bundles for different sections
- **Lazy Loading**: Dynamic imports for non-critical code
- **Compression**: Gzip/Brotli compression

## Scalability Architecture

### Horizontal Scaling
- **Stateless Design**: Session storage in database/Redis
- **Load Balancing**: Multiple application instances
- **Database Scaling**: Read replicas and connection pooling

### Vertical Scaling
- **Resource Optimization**: Memory and CPU optimization
- **Connection Pooling**: Database connection management
- **Process Management**: FrankenPHP worker processes

## Integration Points

### Payment Gateways
- **Payum Integration**: Unified payment processing
- **Gateway Abstraction**: Consistent interface for all gateways
- **Webhook Handling**: Asynchronous payment notifications

### Email Providers
- **Mailer Abstraction**: Symfony Mailer with multiple transports
- **Template Management**: Twig-based email templates
- **Delivery Tracking**: Email delivery status monitoring

### API Integration
- **API Platform**: Automatic API generation
- **Serialization**: Consistent data formatting
- **Versioning**: API version management
- **Documentation**: Automatic OpenAPI documentation

## Development Patterns

### Bundle Development
1. **Entity First**: Define domain entities
2. **Repository**: Create data access layer
3. **Forms**: Build form types and handlers
4. **Controllers**: Implement web controllers
5. **Templates**: Create Twig templates
6. **Tests**: Write comprehensive tests

### Testing Strategy
- **Unit Tests**: Individual component testing
- **Integration Tests**: Component interaction testing
- **Functional Tests**: End-to-end workflow testing
- **API Tests**: REST API endpoint testing

### Code Quality
- **Static Analysis**: PHPStan for type checking
- **Code Style**: ECS for consistent formatting
- **Documentation**: PHPDoc for all public methods
- **Type Hints**: Strict typing throughout codebase

## Future Architecture Considerations

### Microservices Migration
- **Service Boundaries**: Clear domain boundaries already exist
- **API-First**: Existing API Platform foundation
- **Event Sourcing**: Event-driven architecture foundation

### Cloud-Native Features
- **Container Orchestration**: Kubernetes deployment
- **Service Mesh**: Inter-service communication
- **Observability**: Distributed tracing and monitoring

### Performance Enhancements
- **CQRS**: Command Query Responsibility Segregation
- **Event Sourcing**: Event-based state management
- **Caching Layers**: Multi-level caching strategy