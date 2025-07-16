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

### 1. Clone the Repository

```bash
git clone <repository-url>
cd SideQuestCrm
```

### 2. Environment Setup

Copy the environment file and configure it:

```bash
cp .env.example .env
```

Update the `.env` file with your configuration:

```env
APP_NAME="SideQuest CRM"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=sidequest_crm
DB_USERNAME=sidequest_crm
DB_PASSWORD=secret

REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
```

### 3. Start Docker Services

```bash
docker-compose up -d
```

### 4. Install Dependencies

```bash
# Install PHP dependencies
docker-compose exec app composer install

# Install Node.js dependencies
docker-compose exec app npm install
```

### 5. Generate Application Key

```bash
docker-compose exec app php artisan key:generate
```

### 6. Run Database Migrations

```bash
docker-compose exec app php artisan migrate
```

### 7. Install Laravel Jetstream

```bash
docker-compose exec app php artisan jetstream:install livewire --teams
docker-compose exec app php artisan migrate
```

### 8. Build Frontend Assets

```bash
docker-compose exec app npm run build
```

### 9. Create First Tenant

```bash
docker-compose exec app php artisan tinker
```

```php
// Create the first tenant (Rock Hard Chimney)
$tenant = \App\Models\Tenant::create([
    'name' => 'rock-hard',
    'domain' => 'rock-hard.localhost',
    'database' => 'rock_hard_chimney',
    'company_name' => 'Rock Hard Chimney',
    'contact_email' => 'admin@rockhardchimney.com',
    'contact_phone' => '(555) 123-4567',
    'address' => '123 Main St',
    'city' => 'Anytown',
    'state' => 'CA',
    'zip_code' => '90210',
    'country' => 'US',
    'timezone' => 'America/Los_Angeles',
    'is_active' => true,
]);

// Create admin user for the tenant
$user = \App\Models\User::create([
    'tenant_id' => $tenant->id,
    'name' => 'Admin User',
    'email' => 'admin@rockhardchimney.com',
    'password' => bcrypt('password'),
    'position' => 'Administrator',
    'is_active' => true,
]);
```

### 10. Access the Application

- **Main Application**: http://localhost:8000
- **MailHog (Email Testing)**: http://localhost:8025

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
