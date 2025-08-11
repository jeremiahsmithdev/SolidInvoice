# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Quick Start Guide

### Access & Authentication
- **Application URL**: http://localhost:3000
- **Test Login**: jeremiah@symbiotek.com.au / Thr3ftygui!
- **API Key**: `569a168393da35304d9f2a2e0e3fa4305db7efcd9e8e3d36d6b9ca1a9d8c6269`

### Essential Commands
```bash
# Start development environment
docker-compose up -d
docker-compose exec app composer install
bun install

# Watch frontend assets
bun run watch

# Run tests
docker-compose exec app bin/phpunit
bun run lint:js

# Build for production
bun run build
```

## Project Understanding

### **📖 Read First**: Project Overview
- **📁 `/docs/PROJECT_OVERVIEW.md`** - High-level understanding of SolidInvoice
- **📁 `/docs/SCOPE.md`** - Current project scope and tree felling specialization
- **📁 `/docs/ARCHITECTURE.md`** - Technical architecture and design patterns

### **🗺️ Navigation Guide**
- **📁 `/docs/NAVIGATION.md`** - **ESSENTIAL**: How to navigate the codebase
- **📁 `/docs/CODE_CONVENTIONS.md`** - Coding standards and best practices

## Development Guidance by Task Type

### 🚀 **Getting Started**
- **📁 `/docs/DEVELOPMENT_SETUP.md`** - Complete installation instructions
- **📁 `/docs/COMMON_COMMANDS.md`** - Quick reference for daily commands
- **📁 `/docs/DEVELOPMENT_WORKFLOW.md`** - Complete feature development lifecycle

### 🏗️ **Backend Development**
- **📁 `/docs/ARCHITECTURE.md`** - Domain-Driven Design patterns
- **📁 `/docs/DATABASE.md`** - Doctrine ORM, migrations, entity relationships
- **📁 `/docs/DEVELOPMENT_WORKFLOW.md`** - Entity, repository, and form development

### 🎨 **Frontend Development**
- **📁 `/docs/FRONTEND_DEVELOPMENT.md`** - **CRITICAL**: Stimulus controllers, TypeScript, SCSS
- **📁 `/docs/NAVIGATION.md`** - Frontend structure and patterns

#### **Frontend Development Patterns (Stimulus)**
**⚠️ IMPORTANT**: SolidInvoice uses **Stimulus controllers**, NOT inline JavaScript in templates.

**Creating Interactive Features:**
1. **Create Stimulus Controller** (TypeScript):
   ```typescript
   // assets/controllers/feature-name-controller.ts
   import { Controller } from "@hotwired/stimulus"
   
   export default class extends Controller {
     static targets = ["element"]
     static values = { apiUrl: String, apiToken: String }
     
     connect() {
       console.log("Controller connected")
     }
     
     async handleAction() {
       // Feature logic here
     }
   }
   ```

2. **Update Template** (Twig):
   ```twig
   <div data-controller="feature-name" 
        data-feature-name-api-url-value="{{ path('api_endpoint') }}"
        data-feature-name-api-token-value="569a168393da35304d9f2a2e0e3fa4305db7efcd9e8e3d36d6b9ca1a9d8c6269">
     <div data-feature-name-target="element"></div>
     <button data-action="click->feature-name#handleAction">Action</button>
   </div>
   ```

3. **Build Assets**:
   ```bash
   bun run build
   ```

**Example: Client Details Display**
- Controller: `assets/controllers/client-details-controller.ts`
- Template: Use `data-controller`, `data-*-target`, and `data-action` attributes
- API Integration: Fetch client data and display in UI
- See implementation in `src/JobBundle/Resources/views/Default/create.html.twig`

### 🔌 **API Development**
- **📁 `/docs/API_DEVELOPMENT.md`** - REST API with API Platform
- **📁 `/docs/API_TEST.md`** - Testing endpoints and authentication

### 🗃️ **Database Work**
- **📁 `/docs/DATABASE.md`** - Migrations, entities, relationships
- **📁 `/docs/DEVELOPMENT_WORKFLOW.md`** - Entity development patterns

### 🧪 **Testing**
- **📁 `/docs/TESTING.md`** - PHPUnit, Foundry, frontend testing
- **📁 `/docs/DEVELOPMENT_WORKFLOW.md`** - Test-driven development approach

### 🚢 **Deployment**
- **📁 `/docs/BUILD_DEPLOY.md`** - Build processes and deployment strategies

### ⚙️ **Configuration**
- **📁 `/docs/EMAIL_CONFIGURATION.md`** - Email provider setup
- **📁 `/docs/CLIENT_CONTACT_INTEGRATION.md`** - Recent integration work

## Architecture Summary

SolidInvoice uses **Domain-Driven Design** with a modular monolith architecture:

### Tech Stack
- **Backend**: PHP 8.3+, Symfony 7.1, Doctrine ORM, API Platform 4.0
- **Frontend**: TypeScript, Stimulus (Symfony UX), Twig with Live Components, Bootstrap 4/AdminLTE
- **Database**: MySQL 8.0+ or PostgreSQL 13+, uses ULID for primary keys
- **Build Tools**: Bun (v1.2.10), Webpack Encore
- **Development**: Docker Compose, FrankenPHP (Caddy-based)

### Core Business Bundles
- **ClientBundle**: Customer and contact management
- **InvoiceBundle**: Billing and recurring invoices
- **QuoteBundle**: Estimates and quote-to-invoice conversion
- **JobBundle**: Job/project management
- **PaymentBundle**: Payment processing
- **MapBundle**: Geographic visualization

### Key Development Patterns
- **Repository Pattern**: Data access layer
- **Form Handler Pattern**: Business logic encapsulation
- **Event-Driven Architecture**: Domain events for decoupling
- **Factory Pattern**: Test data generation with Foundry
- **Multi-Tenancy**: Company-based data isolation

## Development Best Practices

### Code Organization
1. **Follow Bundle Structure**: Each business domain has its own bundle
2. **Use Existing Patterns**: Check neighboring files for conventions
3. **Entity Development**: Always use ULID primary keys, API Platform attributes
4. **Form Development**: Use Form Handlers, not inline controller logic
5. **Frontend**: Use Stimulus controllers, avoid inline JavaScript

### Testing Requirements
```bash
# Always run before completing tasks
docker-compose exec app vendor/bin/phpstan analyse
docker-compose exec app vendor/bin/ecs check --fix
docker-compose exec app bin/phpunit
bun run lint:js
bun run lint:css
```

### Documentation Updates
- Update `/docs` when making architectural changes
- Follow patterns in **📁 `/docs/DOCUMENTATION_ALIGNMENT.md`**
- Keep CLAUDE.md synchronized with project changes

## Troubleshooting

### Common Issues
- **JavaScript not working**: Check if using Stimulus controllers (not inline JS)
- **API calls failing**: Verify API token and endpoint paths
- **Build errors**: Check TypeScript types and controllers.json registration
- **Database issues**: Run migrations and check entity relationships

### Debugging
- **Symfony logs**: `var/log/dev.log`
- **Browser console**: Check for JavaScript errors
- **API testing**: Use `/docs/API_TEST.md` examples
- **Frontend debugging**: Enable Stimulus debug mode

### Getting Help
- **Architecture questions**: Read **📁 `/docs/ARCHITECTURE.md`**
- **Development workflow**: Check **📁 `/docs/DEVELOPMENT_WORKFLOW.md`**
- **Frontend issues**: Consult **📁 `/docs/FRONTEND_DEVELOPMENT.md`**
- **API problems**: Reference **📁 `/docs/API_DEVELOPMENT.md`**

## Important Development Reminders

### Before Starting Work
1. Read **📁 `/docs/NAVIGATION.md`** for codebase navigation
2. Check **📁 `/docs/DEVELOPMENT_WORKFLOW.md`** for patterns
3. Review **📁 `/docs/CODE_CONVENTIONS.md`** for standards

### For Frontend Features
1. **Never use inline JavaScript** in Twig templates
2. Always create **Stimulus controllers** in TypeScript
3. Use proper **data-controller**, **data-target**, and **data-action** attributes
4. Build assets with `bun run build` after changes
5. Follow patterns in **📁 `/docs/FRONTEND_DEVELOPMENT.md`**

### For Backend Features
1. Follow **Domain-Driven Design** principles
2. Use **ULID** for all primary keys
3. Implement **API Platform** attributes on entities
4. Create **Form Handlers** for business logic
5. Write **comprehensive tests** using Foundry

### Multi-Company Support
- All business data must be **company-scoped**
- Use **CompanyAware** trait on entities
- Implement proper **access controls** in repositories
- Test with multiple company contexts

## Recent Project Context

- **Specialization**: Tree felling and landscaping businesses
- **Recent work**: Client contact integration, interactive maps, job management
- **Current focus**: Enhanced workflow states, SMS notifications, API improvements
- **Architecture**: Maintained backward compatibility while adding new features

---

## 📚 Complete Documentation Index

| Task Type | Primary Reference | Secondary References |
|-----------|------------------|---------------------|
| **Getting Started** | `/docs/DEVELOPMENT_SETUP.md` | `/docs/COMMON_COMMANDS.md` |
| **Understanding Architecture** | `/docs/ARCHITECTURE.md` | `/docs/NAVIGATION.md` |
| **Frontend Development** | `/docs/FRONTEND_DEVELOPMENT.md` | `/docs/CODE_CONVENTIONS.md` |
| **Backend Development** | `/docs/DEVELOPMENT_WORKFLOW.md` | `/docs/DATABASE.md` |
| **API Development** | `/docs/API_DEVELOPMENT.md` | `/docs/API_TEST.md` |
| **Testing** | `/docs/TESTING.md` | `/docs/DEVELOPMENT_WORKFLOW.md` |
| **Deployment** | `/docs/BUILD_DEPLOY.md` | `/docs/COMMON_COMMANDS.md` |
| **Project Context** | `/docs/PROJECT_OVERVIEW.md` | `/docs/SCOPE.md` |

---

# important-instruction-reminders
Do what has been asked; nothing more, nothing less.
NEVER create files unless they're absolutely necessary for achieving your goal.
ALWAYS prefer editing an existing file to creating a new one.
NEVER proactively create documentation files (*.md) or README files. Only create documentation files if explicitly requested by the User.