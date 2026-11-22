# Test Rules for Laravel (PHP)
# Rule 1: Database Integrity
# Description: All database interactions must be validated against expected schema and data types before committing.
# Requirement: Use database migrations for schema changes. All read/write operations must use Eloquent models or Query Builder with explicit casting.
# Verification: Run unit and feature tests covering CRUD operations for all major models.
# Rule 2: Unit Testing Coverage
# Description: All business logic methods within controllers, services, and models must have corresponding unit tests with a minimum 90% line coverage.
# Requirement: Use PHPUnit for all tests. Tests must be isolated and mock external dependencies (e.g., HTTP clients, external services).
# Verification: Run 'vendor/bin/phpunit' and ensure coverage reports meet the 90% threshold.
# Rule 3: API Endpoint Validation
# Description: All exposed API endpoints must return the correct HTTP status code and the expected data structure (JSON schema) for success and failure scenarios.
# Requirement: Use feature tests to validate routes. Check for correct response codes (200, 201, 400, 401, 403, 500).
# Verification: Use feature tests to assert response structure and status codes.
# Rule 4: Environment Safety
# Description: Sensitive configuration (API keys, database credentials) must never be hardcoded.
# Requirement: Use the `.env` file structure and ensure all secrets are loaded via the environment variables.
# Verification: Run a linter to ensure no secrets are present in source code files.

# Test Rules for Node.js (Vite/Tailwind/Alpine.js)
# Rule 1: Component Rendering Fidelity
# Description: All front-end components must render according to the specified design system guidelines (Tailwind CSS classes).
# Requirement: Run end-to-end tests to verify visual output. Verify that dynamic data correctly applies conditional classes.
# Verification: Run Cypress or Playwright tests to inspect DOM structure and style application.
# Rule 2: Asynchronous Handling
# Description: All asynchronous operations (e.g., Axios calls) must handle Promises correctly, including proper error handling and state updates.
# Requirement: Use async/await consistently. Ensure all API calls have appropriate timeouts and error catch blocks.
# Verification: Run tests that specifically mock network responses and confirm state management updates correctly after async operations complete.
# Rule 3: Dependency Integrity
# Description: All installed packages must be validated against known stable versions to prevent breaking changes.
# Requirement: Regularly run 'npm outdated' and patch dependencies immediately.
# Verification: Run 'npm audit' to check for known vulnerabilities.
# Rule 4: Build Process Validation
# Description: The production build process must correctly compile assets and produce optimized output.
# Requirement: Verify the 'npm run build' command executes successfully and generates optimized files.
# Verification: Run the build command and inspect the output directory for correct file generation.