# SideQuest CRM

A multi-tenant CRM system built for handyman businesses, specifically designed for chimney repair and similar service-based companies.

## Features

- **Multi-tenant Architecture**: Database-per-tenant isolation
- **Customer Management**: Track customers, leads, and prospects
- **Job Management**: Schedule and track work performed
- **Estimates & Quotes**: Create and manage estimates
- **Appointment Scheduling**: Schedule site visits and estimates
- **Time Tracking**: Track time spent on jobs
- **Follow-up System**: Manage follow-up tasks and communications
- **Service Management**: Define and track different service types
- **User Management**: Role-based access control
- **Activity Logging**: Track all changes and activities

## Tech Stack

- **Backend**: Laravel 12 with PHP 8.2
- **Frontend**: Vue.js 3 with Pinia for state management
- **Database**: MySQL 8.0
- **Authentication**: Laravel Jetstream
- **Multi-tenancy**: Spatie Laravel Multitenancy
- **Permissions**: Spatie Laravel Permission
- **Activity Logging**: Spatie Laravel Activity Log
- **Containerization**: Docker & Docker Compose

## Prerequisites

- Docker and Docker Compose
- Node.js 18+ (for frontend development)
- Composer (for PHP dependencies)

## Quick Start

### One-Command Setup

```bash
composer sidequest:go
```

This single command will set up everything you need:
- ✅ Check prerequisites (Docker, ports, etc.)
- ✅ Set up environment files
- ✅ Start all Docker containers
- ✅ Wait for services to be ready
- ✅ Install PHP and Node.js dependencies
- ✅ Set up Laravel with Jetstream
- ✅ Build frontend assets
- ✅ Display success message with access URLs

### Access Your Application

- **Main Application**: http://localhost:8000
- **MailHog (Email Testing)**: http://localhost:8025
- **MySQL Database**: localhost:3306
- **Redis Cache**: localhost:6379

### Configuration

The application uses environment variables for all configuration. Key Docker variables include:

```bash
# Port Configuration
DOCKER_WEB_PORT=8000          # Web server port
DOCKER_DB_PORT=3306           # Database port
DOCKER_REDIS_PORT=6379        # Redis port
DOCKER_MAILHOG_SMTP_PORT=1025 # MailHog SMTP port
DOCKER_MAILHOG_UI_PORT=8025   # MailHog UI port

# Database Configuration (REQUIRED - no defaults)
DOCKER_DB_DATABASE=sidequest_crm
DOCKER_DB_USERNAME=sidequest_crm
DOCKER_DB_PASSWORD=your_secure_password_here
DOCKER_DB_ROOT_PASSWORD=your_secure_root_password_here
```

To customize these settings, run:
```bash
composer sidequest:customize
```

### Security

The application requires explicit database credentials to be set in the `.env` file. No default values are provided for security reasons.

**Required Database Variables:**
- `DOCKER_DB_DATABASE` - Database name
- `DOCKER_DB_USERNAME` - Database username  
- `DOCKER_DB_PASSWORD` - Database password
- `DOCKER_DB_ROOT_PASSWORD` - Database root password

To generate secure random secrets:

```bash
composer sidequest:secrets
```

This will generate:
- Secure database passwords
- Laravel application key
- Session encryption keys
- API token secrets

**Important**: 
- Never commit your `.env` file to version control
- The `.env.example` file contains placeholder values for reference
- Setup will fail if database credentials are not explicitly set
- This helps prevent deployment issues and makes debugging easier

### Development Commands

```bash
# Start everything (first time setup)
composer sidequest:go

# Start services only (if already set up)
composer sidequest:up

# Stop all services
composer sidequest:down

# Restart all services
composer sidequest:restart

# Start frontend development mode (hot reloading)
composer sidequest:dev

# Customize Docker configuration
composer sidequest:customize

# Generate secure secrets
composer sidequest:secrets
```

### Manual Setup (Alternative)

If you prefer manual setup, see [DEVELOPMENT.md](DEVELOPMENT.md) for detailed instructions.

## Development

### Running Tests

```bash
docker-compose exec app php artisan test
```

### Code Quality

```bash
# PHP Code Style
docker-compose exec app ./vendor/bin/pint

# PHP Static Analysis
docker-compose exec app ./vendor/bin/phpstan analyse
```

### Database Management

```bash
# Access MySQL
docker-compose exec db mysql -u sidequest_crm -p sidequest_crm

# Run migrations
docker-compose exec app php artisan migrate

# Rollback migrations
docker-compose exec app php artisan migrate:rollback

# Seed database
docker-compose exec app php artisan db:seed
```

## Multi-Tenancy

This CRM uses a database-per-tenant approach for complete data isolation. Each tenant gets their own database with the same schema.

### Tenant Management

- Tenants are identified by subdomain
- Each tenant has their own database
- Users are scoped to their tenant
- All data is automatically scoped to the current tenant

### Adding New Tenants

1. Create a new tenant record in the main database
2. Create a new database for the tenant
3. Run migrations on the new tenant database
4. Create initial users for the tenant

## API Documentation

The application provides a REST API for frontend integration. API documentation will be available at `/api/documentation` when the application is running.

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests for new functionality
5. Ensure all tests pass
6. Submit a pull request

## License

This project is licensed under the MIT License.

## Support

For support and questions, please contact the development team.
