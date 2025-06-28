# API Development Guide

This document covers API development in SolidInvoice using API Platform and best practices for building RESTful APIs.

## Technology Stack

- **API Platform**: Automatic API generation from Doctrine entities
- **Symfony Serializer**: Data serialization and normalization
- **OpenAPI/Swagger**: Automatic API documentation
- **JWT Authentication**: Secure API access
- **Doctrine ORM**: Data persistence layer

## API Architecture

### API Platform Integration

SolidInvoice uses API Platform to automatically generate REST APIs from Doctrine entities:

```php
// src/InvoiceBundle/Entity/Invoice.php
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Post(),
        new Patch(),
        new Delete()
    ],
    normalizationContext: ['groups' => ['invoice:read']],
    denormalizationContext: ['groups' => ['invoice:write']]
)]
#[ORM\Entity(repositoryClass: InvoiceRepository::class)]
class Invoice
{
    #[Groups(['invoice:read', 'invoice:write'])]
    #[ORM\Column(type: 'string')]
    private string $title;

    #[Groups(['invoice:read'])]
    #[ORM\Column(type: 'datetime')]
    private DateTimeInterface $createdAt;
}
```

### API Endpoints

The API follows RESTful conventions:

```
GET    /api/invoices           # List invoices
GET    /api/invoices/{id}      # Get specific invoice
POST   /api/invoices           # Create invoice
PATCH  /api/invoices/{id}      # Update invoice
DELETE /api/invoices/{id}      # Delete invoice

GET    /api/clients            # List clients
GET    /api/clients/{id}       # Get specific client
POST   /api/clients            # Create client
PATCH  /api/clients/{id}       # Update client
DELETE /api/clients/{id}       # Delete client

GET    /api/clients/{clientId}/addresses     # List client addresses
GET    /api/clients/{clientId}/address/{id}  # Get specific address
POST   /api/clients/{clientId}/addresses     # Create address for client
PATCH  /api/clients/{clientId}/address/{id}  # Update address
DELETE /api/clients/{clientId}/address/{id}  # Delete address

GET    /map/api/clients        # Get clients with addresses for mapping
```

## Authentication

### API Token Authentication

SolidInvoice uses API tokens for authentication:

```php
// src/ApiBundle/Security/ApiTokenAuthenticator.php
class ApiTokenAuthenticator extends AbstractAuthenticator
{
    public function supports(Request $request): ?bool
    {
        return $request->headers->has('X-API-TOKEN');
    }

    public function authenticate(Request $request): Passport
    {
        $apiToken = $request->headers->get('X-API-TOKEN');
        
        if (null === $apiToken) {
            throw new CustomUserMessageAuthenticationException('No API token provided');
        }

        return new SelfValidatingPassport(
            new UserBadge($apiToken, function($apiToken) {
                return $this->apiTokenManager->findUserByToken($apiToken);
            })
        );
    }
}
```

### Usage

Include the API token in request headers:

```bash
curl -H "X-API-TOKEN: your-api-token" \
     -H "Content-Type: application/json" \
     https://your-domain.com/api/invoices
```

## Serialization

### Serialization Groups

Control which fields are exposed in API responses:

```php
class Invoice
{
    #[Groups(['invoice:read', 'invoice:write'])]
    private string $title;

    #[Groups(['invoice:read'])]
    private DateTimeInterface $createdAt;

    #[Groups(['invoice:write'])]
    private string $internalNotes;
}
```

### Custom Normalizers

Create custom normalizers for complex data transformation:

```php
// src/ApiBundle/Serializer/Normalizer/CreditNormalizer.php
class CreditNormalizer implements NormalizerInterface
{
    public function normalize($object, string $format = null, array $context = []): array
    {
        assert($object instanceof Credit);

        return [
            'id' => $object->getId(),
            'amount' => [
                'value' => $object->getValue()->getAmount(),
                'currency' => $object->getValue()->getCurrency()->getCode(),
            ],
            'type' => $object->getType(),
        ];
    }

    public function supportsNormalization($data, string $format = null): bool
    {
        return $data instanceof Credit;
    }
}
```

## API Resources

### Basic Resource Configuration

```php
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Post(security: "is_granted('ROLE_USER')"),
        new Patch(security: "is_granted('ROLE_USER') and object.getCompany() == user.getCompany()"),
        new Delete(security: "is_granted('ROLE_ADMIN')")
    ]
)]
class Client
{
    // Entity properties
}
```

### Custom Operations

Define custom API operations:

```php
#[ApiResource(
    operations: [
        new GetCollection(),
        new Get(),
        new Post(),
        new Patch(),
        new Get(
            uriTemplate: '/invoices/{id}/pdf',
            controller: GenerateInvoicePdfController::class,
            name: 'invoice_pdf'
        )
    ]
)]
class Invoice
{
    // Entity properties
}
```

### Filters

Add filtering capabilities to collections:

```php
#[ApiResource]
#[ApiFilter(SearchFilter::class, properties: ['client.name' => 'partial', 'status' => 'exact'])]
#[ApiFilter(DateFilter::class, properties: ['createdAt'])]
#[ApiFilter(OrderFilter::class, properties: ['createdAt', 'total'])]
class Invoice
{
    // Entity properties
}
```

## Request/Response Examples

### Create Invoice

**Request:**
```http
POST /api/invoices
Content-Type: application/json
X-API-TOKEN: your-api-token

{
  "client": "/api/clients/01234567-89ab-cdef-0123-456789abcdef",
  "title": "Tree Removal Service",
  "description": "Remove large oak tree from backyard",
  "lines": [
    {
      "description": "Tree removal",
      "quantity": 1,
      "price": 50000
    }
  ]
}
```

**Response:**
```http
HTTP/1.1 201 Created
Content-Type: application/json

{
  "@context": "/api/contexts/Invoice",
  "@id": "/api/invoices/01234567-89ab-cdef-0123-456789abcdef",
  "@type": "Invoice",
  "id": "01234567-89ab-cdef-0123-456789abcdef",
  "title": "Tree Removal Service",
  "description": "Remove large oak tree from backyard",
  "status": "draft",
  "total": {
    "amount": 50000,
    "currency": "AUD"
  },
  "createdAt": "2024-01-15T10:30:00+00:00",
  "client": {
    "@id": "/api/clients/01234567-89ab-cdef-0123-456789abcdef",
    "name": "John Smith"
  }
}
```

### List Invoices with Filters

```http
GET /api/invoices?client.name=Smith&status=draft&order[createdAt]=desc
X-API-TOKEN: your-api-token
```

### Update Invoice Status

```http
PATCH /api/invoices/01234567-89ab-cdef-0123-456789abcdef
Content-Type: application/merge-patch+json
X-API-TOKEN: your-api-token

{
  "status": "sent"
}
```

### Get Client Map Data

**Request:**
```http
GET /map/api/clients
X-API-TOKEN: your-api-token
```

**Response:**
```json
[
  {
    "id": "01234567-89ab-cdef-0123-456789abcdef",
    "name": "John Smith",
    "email": "john@example.com",
    "address": {
      "street1": "123 Main Street",
      "street2": "Suite 100",
      "city": "Sydney",
      "state": "NSW",
      "zip": "2000",
      "country": "AU",
      "countryName": "Australia",
      "formatted": "123 Main Street, Suite 100, Sydney, NSW 2000, Australia"
    }
  },
  {
    "id": "fedcba98-7654-3210-fedc-ba9876543210",
    "name": "Jane Doe",
    "email": "jane@company.com",
    "address": {
      "street1": "456 Business Ave",
      "street2": "",
      "city": "Melbourne",
      "state": "VIC",
      "zip": "3000",
      "country": "AU",
      "countryName": "Australia",
      "formatted": "456 Business Ave, Melbourne, VIC 3000, Australia"
    }
  }
]
```

This endpoint is specifically designed for map visualization and returns only clients that have valid addresses. The `formatted` field provides a complete address string suitable for geocoding services.

## Error Handling

### Standard Error Responses

API Platform provides consistent error responses:

```json
{
  "@context": "/api/contexts/Error",
  "@type": "hydra:Error",
  "hydra:title": "An error occurred",
  "hydra:description": "Invalid input data",
  "violations": [
    {
      "propertyPath": "email",
      "message": "This value is not a valid email address."
    }
  ]
}
```

### Custom Exception Handling

```php
// src/ApiBundle/EventListener/ExceptionListener.php
class ExceptionListener
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();
        
        if ($exception instanceof CustomBusinessException) {
            $response = new JsonResponse([
                'error' => 'business_rule_violation',
                'message' => $exception->getMessage(),
                'code' => $exception->getCode()
            ], 422);
            
            $event->setResponse($response);
        }
    }
}
```

## Testing APIs

### API Test Cases

```php
// src/ApiBundle/Tests/InvoiceApiTest.php
class InvoiceApiTest extends ApiTestCase
{
    public function testCreateInvoice(): void
    {
        $client = $this->createClient();
        $client->request('POST', '/api/invoices', [
            'headers' => ['X-API-TOKEN' => $this->getApiToken()],
            'json' => [
                'title' => 'Test Invoice',
                'client' => $this->getIriFromItem($this->client),
            ]
        ]);

        $this->assertResponseStatusCodeSame(201);
        $this->assertJsonContains([
            'title' => 'Test Invoice',
            'status' => 'draft'
        ]);
    }

    public function testGetInvoiceCollection(): void
    {
        $client = $this->createClient();
        $client->request('GET', '/api/invoices', [
            'headers' => ['X-API-TOKEN' => $this->getApiToken()]
        ]);

        $this->assertResponseIsSuccessful();
        $this->assertJsonContains([
            '@context' => '/api/contexts/Invoice',
            '@type' => 'hydra:Collection'
        ]);
    }
}
```

### Using Foundry for Test Data

```php
public function testInvoiceWithLines(): void
{
    $invoice = InvoiceFactory::createOne([
        'client' => ClientFactory::createOne(),
        'lines' => LineFactory::createMany(3)
    ]);

    $client = $this->createClient();
    $client->request('GET', '/api/invoices/' . $invoice->getId(), [
        'headers' => ['X-API-TOKEN' => $this->getApiToken()]
    ]);

    $this->assertResponseIsSuccessful();
}
```

## API Documentation

### Automatic Documentation

API Platform automatically generates OpenAPI documentation:

- **Swagger UI**: Available at `/api/docs`
- **OpenAPI Spec**: Available at `/api/docs.json`

### Custom Documentation

Add custom documentation to operations:

```php
#[ApiResource(
    operations: [
        new Get(
            openapi: new Operation(
                summary: 'Retrieve an invoice',
                description: 'Retrieves a specific invoice by ID with all related data',
                tags: ['Invoice Management']
            )
        )
    ]
)]
class Invoice
{
    // Entity properties
}
```

## Security Considerations

### Input Validation

Always validate input data:

```php
class Invoice
{
    #[Assert\NotBlank]
    #[Assert\Length(max: 255)]
    #[Groups(['invoice:write'])]
    private string $title;

    #[Assert\Positive]
    #[Groups(['invoice:write'])]
    private int $total;
}
```

### Access Control

Implement proper access control:

```php
#[ApiResource(
    operations: [
        new GetCollection(security: "is_granted('ROLE_USER')"),
        new Get(security: "is_granted('ROLE_USER') and object.getCompany() == user.getCompany()"),
        new Post(security: "is_granted('ROLE_USER')"),
        new Patch(security: "is_granted('ROLE_USER') and object.getCompany() == user.getCompany()"),
        new Delete(security: "is_granted('ROLE_ADMIN')")
    ]
)]
class Invoice
{
    // Entity properties
}
```

### Rate Limiting

Implement rate limiting for API endpoints:

```yaml
# config/packages/rate_limiter.yaml
framework:
    rate_limiter:
        api:
            policy: 'sliding_window'
            limit: 1000
            interval: '1 hour'
```

## Performance Optimization

### Pagination

Use pagination for large collections:

```php
#[ApiResource(
    paginationItemsPerPage: 25,
    paginationMaximumItemsPerPage: 100
)]
class Invoice
{
    // Entity properties
}
```

### Eager Loading

Optimize database queries:

```php
#[ApiResource(
    operations: [
        new GetCollection(
            provider: InvoiceCollectionProvider::class
        )
    ]
)]
class Invoice
{
    // Entity properties
}

// Custom provider with eager loading
class InvoiceCollectionProvider implements ProviderInterface
{
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        return $this->invoiceRepository->findWithRelations();
    }
}
```

### Caching

Implement HTTP caching:

```php
#[ApiResource(
    operations: [
        new Get(
            cacheHeaders: [
                'max_age' => 3600,
                'shared_max_age' => 7200,
                'vary' => ['Authorization']
            ]
        )
    ]
)]
class Invoice
{
    // Entity properties
}
```

## Best Practices

### API Design

1. **RESTful URLs**: Use resource-based URLs
2. **HTTP Methods**: Use appropriate HTTP methods
3. **Status Codes**: Return meaningful HTTP status codes
4. **Versioning**: Plan for API versioning
5. **Documentation**: Maintain comprehensive documentation

### Data Handling

1. **Validation**: Validate all input data
2. **Serialization**: Use appropriate serialization groups
3. **Normalization**: Implement custom normalizers when needed
4. **Error Handling**: Provide meaningful error messages

### Security

1. **Authentication**: Secure all endpoints
2. **Authorization**: Implement proper access controls
3. **Input Sanitization**: Sanitize user input
4. **Rate Limiting**: Prevent abuse
5. **HTTPS**: Always use HTTPS in production

### Performance

1. **Pagination**: Paginate large collections
2. **Filtering**: Provide filtering capabilities
3. **Caching**: Implement appropriate caching
4. **Database Optimization**: Optimize database queries
5. **Monitoring**: Monitor API performance and usage