# Shortlink Project Overview

This is a Laravel 12 URL shortening service project running on PHP 8.2+. The project provides functionality to create and manage short URLs with domain management.

## Project Purpose
- URL shortening service
- Domain management for shortlinks
- Click tracking and analytics
- User management

## Tech Stack
- **Framework**: Laravel 12
- **PHP Version**: 8.2+
- **Testing**: Pest PHP
- **Repository Pattern**: prettus/l5-repository
- **Additional Packages**: 
  - jenssegers/agent (User agent detection)
  - stevebauman/location (Location services)
  - Laravel Pint (Code formatting)
  - Laravel Sail (Docker development)

## Architecture
The project follows a Repository/Service pattern with:
- **Models**: User, Domain, Shortlink, Click
- **Repositories**: Interface-based repositories for each model
- **Services**: Business logic layer that uses repositories
- **Controllers**: HTTP request handling

## Main Entities
1. **Users**: User management
2. **Domains**: Custom domains for shortlinks
3. **Shortlinks**: The actual short URLs
4. **Clicks**: Analytics/tracking data