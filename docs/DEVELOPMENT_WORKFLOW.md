# Development Workflow

This document outlines the comprehensive development workflow for SolidInvoice, based on analysis of recent feature implementations and established patterns in the codebase. It provides step-by-step guidance for implementing new features, following the Domain-Driven Design architecture.

## Table of Contents

1. [Development Philosophy](#development-philosophy)
2. [Feature Development Lifecycle](#feature-development-lifecycle)
3. [Bundle Development Patterns](#bundle-development-patterns)
4. [Database Development](#database-development)
5. [Frontend Development](#frontend-development)
6. [Testing Strategy](#testing-strategy)
7. [Code Quality & Standards](#code-quality--standards)
8. [Common Development Tasks](#common-development-tasks)
9. [Deployment & Release Process](#deployment--release-process)

## Development Philosophy

SolidInvoice follows these core principles:

- **Domain-Driven Design (DDD)**: Each bundle represents a bounded context
- **Action-Domain-Responder (ADR)**: Single-purpose controller actions
- **Progressive Enhancement**: Frontend functionality that works without JavaScript
- **Test-Driven Development**: Comprehensive test coverage for all features
- **API-First**: All entities are API-enabled by default
- **Backward Compatibility**: Maintain compatibility during feature additions

## Feature Development Lifecycle

### Phase 1: Planning & Design

1. **Define the Domain Boundary**
   - Identify if the feature belongs to an existing bundle or needs a new one
   - Define entities, value objects, and domain services
   - Plan database schema changes

2. **API Design**
   - Define API endpoints and data structures
   - Plan serialization groups and normalization
   - Consider authentication and authorization requirements

3. **UI/UX Planning**
   - Design user interface components
   - Plan Stimulus controllers for interactivity
   - Consider mobile responsiveness

### Phase 2: Implementation

#### Step 1: Entity Development
```bash
# Create or modify entities
src/BundleName/Entity/EntityName.php

# Key patterns:
- Use ULID for primary keys
- Implement API Platform attributes
- Add proper validation constraints
- Include serialization groups
- Use traits for common functionality (TimeStampable, CompanyAware, Archivable)
```

#### Step 2: Database Migration
```bash
# Generate migration
docker-compose exec app bin/console doctrine:migrations:diff

# Review and customize migration
migrations/VersionYYYYMMDDHHMMSS.php

# Apply migration
docker-compose exec app bin/console doctrine:migrations:migrate
```

#### Step 3: Repository Development
```bash
# Create repository with business logic methods
src/BundleName/Repository/EntityRepository.php

# Key patterns:
- Extend ServiceEntityRepository
- Add company-aware queries
- Implement search and filtering methods
- Include statistical methods for dashboards
```

#### Step 4: Form Development
```bash
# Create form types
src/BundleName/Form/Type/EntityType.php

# Create form handlers
src/BundleName/Form/Handler/EntityCreateHandler.php
src/BundleName/Form/Handler/EntityEditHandler.php

# Key patterns:
- Extend AbstractFormHandler for consistency
- Use autocomplete types for entity relationships
- Implement proper validation
- Handle file uploads if needed
```

#### Step 5: Action Development
```bash
# Create controller actions
src/BundleName/Action/Create.php
src/BundleName/Action/Edit.php
src/BundleName/Action/Index.php
src/BundleName/Action/View.php
src/BundleName/Action/Transition.php (for workflow entities)

# Key patterns:
- Single responsibility per action
- Use FormHandler for form processing
- Return Template or FormRequest objects
- Handle edge cases (empty data, permissions)
```

#### Step 6: DataGrid Development
```bash
# Create data grid classes
src/BundleName/DataGrid/BaseEntityGrid.php
src/BundleName/DataGrid/EntityGrid.php

# Key patterns:
- Extend from base grid for consistency
- Add filtering and sorting capabilities
- Include batch actions
- Support context-aware filtering
```

#### Step 7: Menu Integration
```bash
# Create menu classes
src/BundleName/Menu/Builder.php
src/BundleName/Menu/EntityMenu.php

# Register menu in services
src/BundleName/Resources/config/services/services.php
```

#### Step 8: Template Development
```bash
# Create Twig templates
src/BundleName/Resources/views/Default/index.html.twig
src/BundleName/Resources/views/Default/create.html.twig
src/BundleName/Resources/views/Default/edit.html.twig
src/BundleName/Resources/views/Default/view.html.twig

# Key patterns:
- Extend base templates
- Use Twig components for reusability
- Include proper form handling
- Add responsive design classes
```

#### Step 9: Frontend Development
```bash
# Create Stimulus controllers if needed
assets/controllers/entity-controller.ts

# Add SCSS styles
assets/scss/entity.scss

# Key patterns:
- Use TypeScript for type safety
- Follow progressive enhancement
- Implement proper error handling
- Add loading states
```

### Phase 3: Testing

#### Step 1: Test Factory Creation
```bash
# Create Foundry factory
src/BundleName/Test/Factory/EntityFactory.php

# Key patterns:
- Use realistic fake data
- Set up proper relationships
- Include optional fields
- Support different states/scenarios
```

#### Step 2: Unit Testing
```bash
# Create entity tests
src/BundleName/Tests/Entity/EntityTest.php

# Create form handler tests
src/BundleName/Tests/Form/Handler/EntityCreateHandlerTest.php

# Key patterns:
- Test all public methods
- Test validation rules
- Test business logic
- Test edge cases
```

#### Step 3: Functional Testing
```bash
# Create API tests
src/BundleName/Tests/Functional/Api/EntityTest.php

# Create integration tests
src/BundleName/Tests/Functional/EntityWorkflowTest.php

# Key patterns:
- Test complete workflows
- Test API endpoints
- Test user permissions
- Test data persistence
```

### Phase 4: Documentation & Integration

#### Step 1: Bundle Registration
```php
// Add to config/bundles.php
SolidInvoice\BundleName\SolidInvoiceBundleNameBundle::class => ['all' => true],
```

#### Step 2: Routing Configuration
```php
// Update config/routes.php or create bundle-specific routing
```

#### Step 3: Documentation Updates
```bash
# Update relevant documentation files
docs/ARCHITECTURE.md
docs/PROJECT_OVERVIEW.md
README.md
```

## Bundle Development Patterns

### Creating a New Bundle

1. **Bundle Structure Setup**
```bash
src/NewBundle/
├── Action/
├── DependencyInjection/
├── Entity/
├── Form/
│   ├── Handler/
│   └── Type/
├── Repository/
├── Resources/
│   ├── config/
│   │   └── services/
│   ├── translations/
│   └── views/
├── Test/
│   └── Factory/
├── Tests/
├── Twig/
│   └── Components/
└── SolidInvoiceNewBundle.php
```

2. **Bundle Class Implementation**
```php
<?php

declare(strict_types=1);

namespace SolidInvoice\NewBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

final class SolidInvoiceNewBundle extends Bundle
{
    final public const NAMESPACE = __NAMESPACE__;
}
```

3. **Dependency Injection Extension**
```php
<?php

declare(strict_types=1);

namespace SolidInvoice\NewBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

final class SolidInvoiceNewExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services/services.php');
    }
}
```

### Entity Development Patterns

#### Standard Entity Template
```php
<?php

declare(strict_types=1);

namespace SolidInvoice\BundleName\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use Doctrine\ORM\Mapping as ORM;
use SolidInvoice\CoreBundle\Traits\Entity\CompanyAware;
use SolidInvoice\CoreBundle\Traits\Entity\TimeStampable;
use Symfony\Bridge\Doctrine\IdGenerator\UlidGenerator;
use Symfony\Bridge\Doctrine\Types\UlidType;
use Symfony\Component\Uid\Ulid;
use Symfony\Component\Validator\Constraints as Assert;

#[ApiResource(
    operations: [
        new Get(),
        new Post(),
        new GetCollection(),
        new Patch(),
        new Delete(),
    ],
    normalizationContext: ['groups' => ['entity_api:read']],
    denormalizationContext: ['groups' => ['entity_api:write']],
)]
#[ORM\Table(name: EntityName::TABLE_NAME)]
#[ORM\Entity(repositoryClass: EntityRepository::class)]
#[ORM\HasLifecycleCallbacks]
class EntityName implements Stringable
{
    use CompanyAware;
    use TimeStampable;

    final public const TABLE_NAME = 'entity_names';

    #[ORM\Id]
    #[ORM\Column(type: UlidType::NAME, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: UlidGenerator::class)]
    private ?Ulid $id = null;

    // Additional properties...

    public function getId(): ?Ulid
    {
        return $this->id;
    }

    public function __toString(): string
    {
        // Implementation
    }
}
```

#### Key Entity Patterns

1. **Use ULID for Primary Keys**
   - Provides better performance than UUID
   - Lexicographically sortable
   - URL-safe

2. **API Platform Integration**
   - Always include API resource attributes
   - Define proper serialization groups
   - Include all CRUD operations by default

3. **Common Traits Usage**
   - `CompanyAware`: For multi-tenant data isolation
   - `TimeStampable`: For created/updated timestamps
   - `Archivable`: For soft delete functionality

4. **Validation Constraints**
   - Use Symfony validation attributes
   - Include business rule validation
   - Add custom validators when needed

### Action Development Patterns

#### Standard Action Template
```php
<?php

declare(strict_types=1);

namespace SolidInvoice\BundleName\Action;

use SolidInvoice\CoreBundle\Templating\Template;
use SolidInvoice\BundleName\Entity\EntityName;
use SolidInvoice\BundleName\Form\Handler\EntityCreateHandler;
use SolidWorx\FormHandler\FormHandler;
use SolidWorx\FormHandler\FormRequest;
use Symfony\Component\HttpFoundation\Request;

final class Create
{
    public function __construct(
        private readonly FormHandler $handler
    ) {
    }

    public function __invoke(Request $request): Template|FormRequest
    {
        $entity = new EntityName();
        
        $options = [
            'entity' => $entity,
            'form_options' => [],
        ];

        return $this->handler->handle(EntityCreateHandler::class, $options);
    }
}
```

#### Action Patterns

1. **Single Responsibility**
   - Each action handles one specific operation
   - Use dependency injection for services
   - Return Template or FormRequest objects

2. **Form Handling**
   - Use FormHandler for consistent form processing
   - Pass entity and options to form handlers
   - Handle edge cases (empty data, validation errors)

3. **Error Handling**
   - Use try-catch for exceptional cases
   - Return appropriate error templates
   - Log errors for debugging

## Database Development

### Migration Best Practices

1. **Always Review Generated Migrations**
```php
// migrations/VersionYYYYMMDDHHMMSS.php
public function getDescription(): string
{
    return 'Descriptive message about what this migration does';
}

public function up(Schema $schema): void
{
    // Use heredoc for complex SQL
    $this->addSql(<<<'SQL'
        CREATE TABLE example_table (
            id BLOB NOT NULL,
            name VARCHAR(255) NOT NULL,
            PRIMARY KEY(id)
        )
    SQL);
}
```

2. **Index Strategy**
   - Add indexes for foreign keys
   - Index frequently queried columns
   - Consider composite indexes for complex queries

3. **Data Migration**
   - Include data transformations when needed
   - Use separate migrations for schema and data changes
   - Test migrations on production-like data

### Repository Patterns

```php
<?php

declare(strict_types=1);

namespace SolidInvoice\BundleName\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use SolidInvoice\BundleName\Entity\EntityName;

/**
 * @extends ServiceEntityRepository<EntityName>
 */
class EntityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, EntityName::class);
    }

    public function findByCompany(Company $company): array
    {
        return $this->createQueryBuilder('e')
            ->where('e.company = :company')
            ->setParameter('company', $company)
            ->getQuery()
            ->getResult();
    }

    public function getStatistics(Company $company): array
    {
        // Business logic for statistics
    }
}
```

## Frontend Development

### Stimulus Controller Patterns

```typescript
// assets/controllers/entity-controller.ts
import { Controller } from '@hotwired/stimulus';

interface EntityData {
    id: string;
    name: string;
    // Other properties
}

export default class extends Controller {
    static targets = ['form', 'list', 'modal'];
    static values = { 
        url: String,
        entityId: String 
    };

    declare readonly formTarget: HTMLFormElement;
    declare readonly listTarget: HTMLElement;
    declare readonly modalTarget: HTMLElement;
    declare readonly urlValue: string;
    declare readonly entityIdValue: string;

    connect() {
        this.loadData();
    }

    async loadData() {
        try {
            const response = await fetch(this.urlValue);
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            const data: EntityData[] = await response.json();
            this.updateList(data);
        } catch (error) {
            console.error('Failed to load data:', error);
            this.showError('Failed to load data');
        }
    }

    private updateList(data: EntityData[]) {
        // Update DOM with data
    }

    private showError(message: string) {
        // Show error message to user
    }
}
```

### SCSS Organization

```scss
// assets/scss/entity.scss
.entity {
    &__container {
        // Container styles
    }

    &__header {
        // Header styles
    }

    &__form {
        // Form styles
    }

    &__list {
        // List styles
    }

    // Responsive design
    @media (max-width: 768px) {
        &__container {
            // Mobile styles
        }
    }
}
```

### Twig Component Patterns

```php
<?php

namespace SolidInvoice\BundleName\Twig\Components;

use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent(name: 'EntityComponent')]
final class EntityComponent
{
    use DefaultActionTrait;

    #[LiveProp(writable: true)]
    public string $searchTerm = '';

    #[LiveProp]
    public array $entities = [];

    public function getFilteredEntities(): array
    {
        if (empty($this->searchTerm)) {
            return $this->entities;
        }

        return array_filter($this->entities, function ($entity) {
            return stripos($entity['name'], $this->searchTerm) !== false;
        });
    }
}
```

## Testing Strategy

### Test Factory Patterns

```php
<?php

namespace SolidInvoice\BundleName\Test\Factory;

use SolidInvoice\BundleName\Entity\EntityName;
use Zenstruck\Foundry\Persistence\PersistentProxyObjectFactory;

/**
 * @extends PersistentProxyObjectFactory<EntityName>
 */
final class EntityFactory extends PersistentProxyObjectFactory
{
    public static function class(): string
    {
        return EntityName::class;
    }

    protected function defaults(): array|callable
    {
        return [
            'name' => self::faker()->company(),
            'description' => self::faker()->sentence(),
            'status' => EntityName::STATUS_ACTIVE,
            'client' => ClientFactory::new(),
        ];
    }

    public function pending(): static
    {
        return $this->with(['status' => EntityName::STATUS_PENDING]);
    }

    public function completed(): static
    {
        return $this->with(['status' => EntityName::STATUS_COMPLETED]);
    }
}
```

### Unit Test Patterns

```php
<?php

namespace SolidInvoice\BundleName\Tests\Entity;

use PHPUnit\Framework\TestCase;
use SolidInvoice\BundleName\Entity\EntityName;

/**
 * @covers \SolidInvoice\BundleName\Entity\EntityName
 */
final class EntityTest extends TestCase
{
    public function testEntityCreation(): void
    {
        $entity = new EntityName();
        $entity->setName('Test Entity');
        $entity->setStatus(EntityName::STATUS_ACTIVE);

        self::assertSame('Test Entity', $entity->getName());
        self::assertSame(EntityName::STATUS_ACTIVE, $entity->getStatus());
        self::assertTrue($entity->isActive());
    }

    public function testEntityValidation(): void
    {
        $entity = new EntityName();
        
        // Test validation constraints
        $violations = $this->validator->validate($entity);
        self::assertCount(1, $violations); // Name is required
    }

    public function testEntityBusinessLogic(): void
    {
        $entity = new EntityName();
        $entity->setStatus(EntityName::STATUS_PENDING);
        
        $entity->activate();
        
        self::assertTrue($entity->isActive());
        self::assertFalse($entity->isPending());
    }
}
```

### Functional Test Patterns

```php
<?php

namespace SolidInvoice\BundleName\Tests\Functional\Api;

use SolidInvoice\ApiBundle\Test\ApiTestCase;
use SolidInvoice\BundleName\Test\Factory\EntityFactory;

final class EntityTest extends ApiTestCase
{
    public function testGetCollection(): void
    {
        EntityFactory::createMany(3);

        $response = $this->client->request('GET', '/api/entities');

        self::assertResponseIsSuccessful();
        self::assertJsonContains([
            '@context' => '/api/contexts/Entity',
            '@type' => 'hydra:Collection',
            'hydra:totalItems' => 3,
        ]);
    }

    public function testCreateEntity(): void
    {
        $response = $this->client->request('POST', '/api/entities', [
            'json' => [
                'name' => 'Test Entity',
                'description' => 'Test Description',
            ],
        ]);

        self::assertResponseStatusCodeSame(201);
        self::assertJsonContains([
            'name' => 'Test Entity',
            'description' => 'Test Description',
        ]);
    }

    public function testUpdateEntity(): void
    {
        $entity = EntityFactory::createOne();

        $response = $this->client->request('PATCH', '/api/entities/' . $entity->getId(), [
            'json' => [
                'name' => 'Updated Name',
            ],
            'headers' => [
                'Content-Type' => 'application/merge-patch+json',
            ],
        ]);

        self::assertResponseIsSuccessful();
        self::assertJsonContains([
            'name' => 'Updated Name',
        ]);
    }
}
```

## Code Quality & Standards

### Static Analysis

```bash
# PHPStan - Static Analysis
docker-compose exec app vendor/bin/phpstan analyse

# ECS - Code Style
docker-compose exec app vendor/bin/ecs check --fix

# ESLint - JavaScript/TypeScript
bun run lint:js

# Stylelint - SCSS
bun run lint:css
```

### Pre-commit Hooks

The project uses pre-commit hooks to ensure code quality:

```yaml
# .pre-commit-config.yaml
repos:
  - repo: local
    hooks:
      - id: phpstan
        name: PHPStan
        entry: docker-compose exec -T app vendor/bin/phpstan analyse
        language: system
        types: [php]
        
      - id: ecs
        name: EasyCodingStandard
        entry: docker-compose exec -T app vendor/bin/ecs check
        language: system
        types: [php]
```

### Code Review Checklist

- [ ] **Architecture Compliance**
  - Follows DDD principles
  - Proper bundle organization
  - Single responsibility principle

- [ ] **Database Design**
  - Proper indexing
  - Foreign key constraints
  - Migration quality

- [ ] **API Design**
  - Consistent endpoint naming
  - Proper HTTP status codes
  - Serialization groups

- [ ] **Frontend Quality**
  - Progressive enhancement
  - Responsive design
  - Accessibility compliance

- [ ] **Testing Coverage**
  - Unit tests for business logic
  - Functional tests for workflows
  - API tests for endpoints

- [ ] **Documentation**
  - Code comments for complex logic
  - API documentation updates
  - User documentation updates

## Common Development Tasks

### Adding a New Field to Existing Entity

1. **Update Entity**
```php
// Add property with proper attributes
#[ORM\Column(type: 'string', length: 255, nullable: true)]
#[Assert\Length(max: 255)]
#[Serialize\Groups(['entity_api:read', 'entity_api:write'])]
private ?string $newField = null;

// Add getter/setter methods
```

2. **Generate Migration**
```bash
docker-compose exec app bin/console doctrine:migrations:diff
```

3. **Update Form Type**
```php
// Add field to form
$builder->add('newField', TextType::class, [
    'required' => false,
    'label' => 'entity.form.new_field',
]);
```

4. **Update Templates**
```twig
{# Add field to forms and views #}
{{ form_row(form.newField) }}
```

5. **Update Tests**
```php
// Add tests for new field
public function testNewField(): void
{
    $entity = new EntityName();
    $entity->setNewField('test value');
    
    self::assertSame('test value', $entity->getNewField());
}
```

### Adding a New API Endpoint

1. **Create Custom Action**
```php
<?php

namespace SolidInvoice\BundleName\Action\Api;

use SolidInvoice\BundleName\Entity\EntityName;
use Symfony\Component\HttpFoundation\JsonResponse;

final class CustomEndpoint
{
    public function __invoke(EntityName $entity): JsonResponse
    {
        // Custom logic
        return new JsonResponse(['result' => 'success']);
    }
}
```

2. **Configure API Platform**
```php
// In entity class
#[ApiResource(
    operations: [
        // ... existing operations
        new Get(
            uriTemplate: '/entities/{id}/custom',
            controller: CustomEndpoint::class,
            name: 'entity_custom'
        ),
    ]
)]
```

3. **Add Tests**
```php
public function testCustomEndpoint(): void
{
    $entity = EntityFactory::createOne();
    
    $response = $this->client->request('GET', '/api/entities/' . $entity->getId() . '/custom');
    
    self::assertResponseIsSuccessful();
    self::assertJsonContains(['result' => 'success']);
}
```

### Adding a New Stimulus Controller

1. **Create Controller File**
```typescript
// assets/controllers/new-feature-controller.ts
import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['element'];
    static values = { config: Object };

    connect() {
        console.log('New feature controller connected');
    }

    handleAction() {
        // Implementation
    }
}
```

2. **Register in Template**
```twig
<div data-controller="new-feature" 
     data-new-feature-config-value="{{ config|json_encode }}">
    <button data-action="click->new-feature#handleAction">
        Click me
    </button>
</div>
```

3. **Add Styles**
```scss
// assets/scss/new-feature.scss
.new-feature {
    &__button {
        // Button styles
    }
}
```

## Deployment & Release Process

### Development Workflow

1. **Feature Branch Creation**
```bash
git checkout -b feature/new-feature-name
```

2. **Development Process**
   - Follow TDD approach
   - Write tests first
   - Implement feature
   - Ensure all tests pass
   - Run code quality checks

3. **Code Review**
   - Create pull request
   - Address review feedback
   - Ensure CI passes

4. **Merge & Deploy**
   - Merge to main branch
   - Deploy to staging
   - Run integration tests
   - Deploy to production

### Release Checklist

- [ ] **Code Quality**
  - All tests passing
  - Static analysis clean
  - Code style compliant

- [ ] **Database**
  - Migrations tested
  - Backup strategy in place
  - Rollback plan prepared

- [ ] **Documentation**
  - API documentation updated
  - User documentation updated
  - Changelog updated

- [ ] **Deployment**
  - Environment variables configured
  - Assets compiled
  - Cache cleared

### Commit Message Convention

Follow conventional commit format:

```
feat(bundle): add new feature description

- Detailed description of changes
- Include breaking changes if any
- Reference issue numbers

🤖 Generated with [Claude Code](https://claude.ai/code)

Co-Authored-By: Claude <noreply@anthropic.com>
```

Types:
- `feat`: New feature
- `fix`: Bug fix
- `docs`: Documentation changes
- `style`: Code style changes
- `refactor`: Code refactoring
- `test`: Test additions/changes
- `chore`: Maintenance tasks

This workflow ensures consistent, high-quality development while maintaining the architectural integrity of SolidInvoice's Domain-Driven Design approach.