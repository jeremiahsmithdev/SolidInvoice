# Code Conventions

This document outlines the coding conventions and best practices to be followed when contributing to SolidInvoice. Adhering to these conventions ensures code consistency, readability, and maintainability.

## Contributing

For information on contributing to the project, please refer to the [CONTRIBUTING](../CONTRIBUTING.md) file.

## License

SolidInvoice is licensed under the MIT license, an open-source software license. For detailed information, please consult the [LICENSE](../LICENSE) file.

## PHP (Symfony)

*   **PSR Standards:** Follow PSR-1, PSR-2, PSR-4, and PSR-12 coding standards.
*   **Symfony Best Practices:** Adhere to Symfony's official best practices for directory structure, configuration, and component usage.
*   **Static Analysis:** Ensure your code passes checks from:
    *   **PHPStan:** Strict type checking and error detection. (Configuration: `phpstan.neon`)
    *   **ECS (EasyCodingStandard):** Code style fixer and linter. (Configuration: `ecs.php`)
*   **Naming Conventions:**
    *   Classes: PascalCase (e.g., `InvoiceController`, `UserRepository`).
    *   Methods/Functions: camelCase (e.g., `getInvoice`, `calculateTotal`).
    *   Variables: camelCase (e.g., `invoiceData`, `userId`).
    *   Constants: SCREAMING_SNAKE_CASE (e.g., `STATUS_PENDING`).
*   **DocBlocks:** Use PHPDoc blocks for classes, methods, and properties to describe their purpose, parameters, and return types.
*   **Dependency Injection:** Prefer dependency injection over direct instantiation where appropriate.

## JavaScript/TypeScript

*   **ESLint:** Adhere to the ESLint rules defined in `.eslintrc` and `eslint.config.mjs`.
*   **TypeScript:** Utilize TypeScript for type safety and improved code quality. Ensure proper type annotations.
*   **Naming Conventions:**
    *   Variables/Functions: camelCase.
    *   Classes: PascalCase.
    *   Constants: SCREAMING_SNAKE_CASE.
*   **Module Imports:** Use absolute paths for internal modules where possible, or consistent relative paths.
*   **Asynchronous Code:** Use `async/await` for asynchronous operations.

## SCSS (Sass)

*   **Stylelint:** Adhere to the Stylelint rules defined in `.stylelintrc.json`.
*   **BEM Methodology:** Consider using BEM (Block-Element-Modifier) for CSS class naming to improve modularity and reusability.
*   **Variables:** Use SCSS variables for colors, fonts, and common values defined in `assets/scss/colors.scss` and `assets/scss/theme.scss`.
*   **Nesting:** Use nesting sparingly and only when it improves readability and logical grouping.

## General

*   **Comments:** Add comments to explain complex logic, non-obvious decisions, or workarounds. Avoid commenting on obvious code.
*   **Readability:** Write clear, concise, and self-documenting code.
*   **Error Handling:** Implement robust error handling and logging.
*   **Security:** Be mindful of security best practices (e.g., input validation, escaping output, preventing SQL injection/XSS).
