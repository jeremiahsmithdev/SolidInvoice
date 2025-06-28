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
- **Entities**: Client, Contact, Address, AdditionalContactDetail, ContactType, Credit
- **Key Features**: Multi-contact support, flexible address management, credit tracking, geographic data
- **API Endpoints**: Full CRUD via API Platform with nested address resources
- **Address System**: Supports multiple addresses per client with country validation
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

#### MapBundle
**Purpose**: Geographic visualization and mapping features
- **Entities**: None (leverages ClientBundle addresses)
- **Key Features**: Interactive Leaflet.js maps, client location visualization, geocoding integration
- **Technology**: Stimulus controllers, OpenStreetMap tiles, Nominatim geocoding
- **API Endpoints**: `/map/api/clients` for client location data
- **Dependencies**: ClientBundle, CoreBundle

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

### Client and Address Management Workflow

```
1. User creates/updates client via form or API
2. Client entity persisted with basic info (name, email)
3. Address entities created as separate records
4. Address validation includes country code verification
5. Multiple addresses supported per client
6. Additional contacts can be added with custom contact types
7. Credit tracking maintained separately
8. Map visualization geocodes addresses for display
```

### Map Visualization Workflow

```
1. User accesses /map page
2. Map controller initializes Leaflet.js map
3. Client data fetched from /map/api/clients endpoint
4. Each client address geocoded via Nominatim API
5. Markers placed on map with client information popups
6. Map auto-zooms to fit all client locations
7. Error handling for failed geocoding attempts
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

## Database Schema Architecture

### Multi-Tenancy Design
All entities implement company-aware architecture for multi-tenant data isolation:

```sql
-- Every table includes company_id for tenant separation
CREATE TABLE clients (
    id ULID PRIMARY KEY,
    company_id ULID NOT NULL,
    firstName VARCHAR(125) NOT NULL,
    lastName VARCHAR(125),
    email VARCHAR(255) NOT NULL,
    status VARCHAR(25),
    created DATETIME NOT NULL,
    updated DATETIME NOT NULL,
    archived DATETIME,
    UNIQUE(email, company_id),
    FOREIGN KEY (company_id) REFERENCES companies(id)
);
```

### Address Management Schema
Flexible address system supporting multiple addresses per client:

```sql
CREATE TABLE addresses (
    id ULID PRIMARY KEY,
    client_id ULID,
    company_id ULID NOT NULL,
    street1 VARCHAR(255),
    street2 VARCHAR(255),
    city VARCHAR(255),
    state VARCHAR(255),
    zip VARCHAR(255),
    country VARCHAR(255), -- ISO country codes
    created DATETIME NOT NULL,
    updated DATETIME NOT NULL,
    FOREIGN KEY (client_id) REFERENCES clients(id),
    FOREIGN KEY (company_id) REFERENCES companies(id)
);
```

### Extensible Contact System
Dynamic contact fields through contact types:

```sql
CREATE TABLE contact_types (
    id ULID PRIMARY KEY,
    company_id ULID NOT NULL,
    name VARCHAR(45) NOT NULL, -- "Phone", "Mobile", "Fax"
    type VARCHAR(45) NOT NULL, -- "tel", "email", "text"
    field_options ARRAY,       -- JSON configuration
    required BOOLEAN NOT NULL,
    UNIQUE(name, company_id)
);

CREATE TABLE contact_details (
    id ULID PRIMARY KEY,
    contact_id ULID,
    contact_type_id ULID,
    company_id ULID NOT NULL,
    value TEXT NOT NULL,
    created DATETIME NOT NULL,
    updated DATETIME NOT NULL,
    FOREIGN KEY (contact_id) REFERENCES contacts(id),
    FOREIGN KEY (contact_type_id) REFERENCES contact_types(id)
);
```

### ULID Primary Keys
Uses ULIDs (Universally Unique Lexicographically Sortable Identifier) for better distributed system support and performance.

## Integration Points

### Payment Gateways
- **Payum Integration**: Unified payment processing
- **Gateway Abstraction**: Consistent interface for all gateways
- **Webhook Handling**: Asynchronous payment notifications

### Email Providers
- **Mailer Abstraction**: Symfony Mailer with multiple transports
- **Template Management**: Twig-based email templates
- **Delivery Tracking**: Email delivery status monitoring

### Geographic Services
- **OpenStreetMap**: Free tile service for map rendering
- **Nominatim Geocoding**: Address-to-coordinate conversion
- **Leaflet.js**: Interactive map rendering library
- **Error Resilience**: Graceful handling of geocoding failures

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