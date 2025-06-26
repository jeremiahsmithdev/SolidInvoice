# Frontend Development Guide

This document covers frontend development practices, patterns, and tools used in SolidInvoice.

## Technology Stack

### Core Technologies
- **TypeScript**: Type-safe JavaScript development
- **Stimulus**: Modest JavaScript framework for progressive enhancement
- **SCSS**: CSS preprocessing with Bootstrap 4 base
- **Webpack Encore**: Asset compilation and bundling
- **Twig**: Server-side templating
- **AdminLTE**: Admin dashboard theme

### Build Tools
- **Bun**: JavaScript package manager and runtime
- **Webpack**: Module bundler and asset processor
- **ESLint**: JavaScript/TypeScript linting
- **Stylelint**: SCSS/CSS linting

## Project Structure

```
assets/
├── controllers/           # Stimulus controllers
│   ├── billing-id-controller.ts
│   ├── bootstrap-modal-controller.ts
│   ├── capture-payment-controller.ts
│   ├── mailsettings-controller.ts
│   └── vat-validator-controller.ts
├── scss/                 # Stylesheets
│   ├── app.scss         # Main application styles
│   ├── billing.scss     # Billing-specific styles
│   ├── email.scss       # Email template styles
│   ├── pdf.scss         # PDF generation styles
│   └── ...
├── img/                 # Images and icons
├── core.ts             # Main JavaScript entry point
└── controllers.json    # Stimulus controller configuration
```

## Stimulus Controllers

### Creating a New Controller

1. **Generate the controller file:**
   ```typescript
   // assets/controllers/my-feature-controller.ts
   import { Controller } from "@hotwired/stimulus"

   export default class extends Controller {
     static targets = ["input", "output"]
     static values = { url: String }

     connect() {
       console.log("MyFeature controller connected")
     }

     handleAction() {
       // Controller logic here
     }
   }
   ```

2. **Register in controllers.json:**
   ```json
   {
     "controllers": {
       "my-feature": {
         "enabled": true,
         "fetch": "eager"
       }
     }
   }
   ```

3. **Use in Twig templates:**
   ```twig
   <div data-controller="my-feature" 
        data-my-feature-url-value="{{ path('api_endpoint') }}">
     <input data-my-feature-target="input" type="text">
     <div data-my-feature-target="output"></div>
     <button data-action="click->my-feature#handleAction">Submit</button>
   </div>
   ```

### Existing Controllers

#### billing-id-controller.ts
Handles billing ID generation and validation.

#### bootstrap-modal-controller.ts
Manages Bootstrap modal interactions and AJAX loading.

#### capture-payment-controller.ts
Handles payment capture workflows and form submissions.

#### mailsettings-controller.ts
Manages email configuration and testing.

#### vat-validator-controller.ts
Validates VAT numbers and handles tax calculations.

## Styling with SCSS

### File Organization

```scss
// assets/scss/app.scss - Main entry point
@import "colors";
@import "theme";
@import "bootstrap";
@import "components/forms";
@import "components/buttons";
@import "pages/invoice";
```

### Color System

```scss
// assets/scss/colors.scss
$primary-color: #007bff;
$secondary-color: #6c757d;
$success-color: #28a745;
$danger-color: #dc3545;
$warning-color: #ffc107;
$info-color: #17a2b8;
```

### Component Styling

Use BEM methodology for CSS classes:

```scss
.invoice-form {
  &__header {
    padding: 1rem;
    border-bottom: 1px solid #dee2e6;
  }

  &__body {
    padding: 1.5rem;
  }

  &__footer {
    padding: 1rem;
    background-color: #f8f9fa;
  }
}
```

## Twig Components

### Live Components

SolidInvoice uses Symfony UX Live Components for reactive interfaces:

```php
// src/CoreBundle/Twig/Components/BootstrapModal.php
#[AsLiveComponent]
class BootstrapModal extends AbstractController
{
    #[LiveProp]
    public string $title = '';

    #[LiveProp]
    public string $content = '';

    #[LiveAction]
    public function save(): void
    {
        // Handle save action
    }
}
```

```twig
{# templates/components/BootstrapModal.html.twig #}
<div class="modal" data-controller="bootstrap-modal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">{{ title }}</h5>
      </div>
      <div class="modal-body">
        {{ content|raw }}
      </div>
      <div class="modal-footer">
        <button data-action="live#action" data-live-action-param="save">
          Save
        </button>
      </div>
    </div>
  </div>
</div>
```

### Twig Components

Regular Twig components for reusable UI elements:

```php
// src/ClientBundle/Twig/Components/ClientForm.php
#[AsTwigComponent]
class ClientForm
{
    public function __construct(
        private FormFactoryInterface $formFactory
    ) {}

    public function getForm(): FormView
    {
        return $this->formFactory->create(ClientType::class)->createView();
    }
}
```

## Asset Management

### Development Workflow

1. **Start the watcher:**
   ```bash
   bun run watch
   ```

2. **Make changes to assets**

3. **Assets are automatically recompiled**

### Production Build

```bash
# Install dependencies
bun install

# Build optimized assets
bun run build
```

### Asset Versioning

Webpack Encore automatically versions assets in production:

```twig
{# Automatically includes versioned assets #}
{{ encore_entry_link_tags('app') }}
{{ encore_entry_script_tags('core') }}
```

## TypeScript Development

### Type Definitions

Create type definitions for better development experience:

```typescript
// assets/types/api.ts
export interface Invoice {
  id: string;
  client: Client;
  total: number;
  status: InvoiceStatus;
  createdAt: Date;
}

export interface Client {
  id: string;
  name: string;
  email: string;
}

export type InvoiceStatus = 'draft' | 'pending' | 'paid' | 'cancelled';
```

### API Integration

```typescript
// assets/services/api.ts
export class ApiClient {
  private baseUrl: string;

  constructor(baseUrl: string) {
    this.baseUrl = baseUrl;
  }

  async fetchInvoices(): Promise<Invoice[]> {
    const response = await fetch(`${this.baseUrl}/api/invoices`);
    return response.json();
  }

  async updateInvoice(id: string, data: Partial<Invoice>): Promise<Invoice> {
    const response = await fetch(`${this.baseUrl}/api/invoices/${id}`, {
      method: 'PATCH',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(data),
    });
    return response.json();
  }
}
```

## Testing Frontend Code

### Manual Testing

1. **Compile assets:**
   ```bash
   bun run build
   ```

2. **Check for compilation errors**

3. **Test in browser**

### Linting

```bash
# JavaScript/TypeScript
bun run lint:js

# SCSS
bun run lint:css
```

## Performance Optimization

### Code Splitting

Webpack Encore automatically splits code into chunks:

```javascript
// webpack.config.js
Encore
  .splitEntryChunks()
  .enableSingleRuntimeChunk()
```

### Lazy Loading

Use dynamic imports for non-critical code:

```typescript
// Load heavy libraries only when needed
async function loadChartLibrary() {
  const { Chart } = await import('chart.js');
  return Chart;
}
```

### Asset Optimization

- **Images**: Optimize images before adding to assets
- **Fonts**: Use web fonts efficiently
- **CSS**: Remove unused styles
- **JavaScript**: Tree shake unused code

## Best Practices

### Stimulus Controllers

1. **Keep controllers focused**: One responsibility per controller
2. **Use targets and values**: Leverage Stimulus data attributes
3. **Handle cleanup**: Disconnect event listeners in `disconnect()`
4. **Progressive enhancement**: Ensure functionality works without JavaScript

### SCSS

1. **Use variables**: Define colors, fonts, and spacing as variables
2. **Modular approach**: Split styles into logical files
3. **BEM methodology**: Use consistent naming conventions
4. **Responsive design**: Mobile-first approach

### TypeScript

1. **Strict typing**: Enable strict mode in tsconfig.json
2. **Interface definitions**: Define interfaces for data structures
3. **Error handling**: Proper error handling for async operations
4. **Code organization**: Group related functionality

### Performance

1. **Minimize HTTP requests**: Bundle assets efficiently
2. **Optimize images**: Use appropriate formats and sizes
3. **Cache assets**: Leverage browser caching
4. **Lazy load**: Load non-critical resources on demand

## Debugging

### Browser DevTools

1. **Console**: Check for JavaScript errors
2. **Network**: Monitor asset loading
3. **Elements**: Inspect DOM and styles
4. **Sources**: Debug TypeScript with source maps

### Stimulus Debugging

```typescript
// Enable Stimulus debug mode
import { Application } from "@hotwired/stimulus"
import { definitionsFromContext } from "@symfony/stimulus-bridge"

const application = Application.start()
application.debug = true // Enable debug mode
```

### Common Issues

1. **Assets not loading**: Check Webpack Encore configuration
2. **Styles not applying**: Verify SCSS import order
3. **Controllers not connecting**: Check data-controller attributes
4. **TypeScript errors**: Review type definitions and imports