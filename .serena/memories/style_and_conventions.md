# Code Style and Conventions

## PHP Standards
- **PSR-4**: Autoloading standard
- **Laravel Conventions**: Follow Laravel naming conventions
- **Repository Pattern**: Using prettus/l5-repository

## Naming Conventions
- **Classes**: PascalCase (e.g., `DomainService`, `UserRepository`)
- **Methods**: camelCase (e.g., `findById`, `createDomain`)
- **Variables**: camelCase (e.g., `$domainService`, `$userData`)
- **Constants**: UPPER_SNAKE_CASE (e.g., `DOMAIN_NOT_FOUND`)
- **Database**: snake_case for tables and columns

## File Structure
- **Controllers**: `app/Http/Controllers/`
- **Models**: `app/Models/`
- **Services**: `app/Services/{Entity}/`
- **Repositories**: `app/Repositories/{Entity}/`
- **Interfaces**: Prefixed with 'I' (e.g., `IDomainRepository`)

## Service Layer Pattern
- Services extend `BaseService` abstract class
- Services inject repository interfaces via constructor
- Controllers use services, not repositories directly
- Services contain business logic, repositories handle data access

## Documentation
- Use PHPDoc comments for methods
- Include parameter and return types
- Group related methods with section comments

## Architecture Patterns
- **Repository Pattern**: Data access abstraction
- **Service Layer**: Business logic separation
- **Dependency Injection**: Constructor injection for dependencies
- **Interface Segregation**: Separate interfaces for repositories