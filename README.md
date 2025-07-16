# SideQuest CRM

A CRM system built for independent contractors and handymen to organize their side jobs. Turn your hobby work into organized, professional business operations.

## What It Does

SideQuest CRM helps contractors, handymen, and side-hustle professionals:
- Track customers and jobs
- Create estimates and quotes
- Schedule appointments
- Log time and expenses
- Manage follow-ups
- Organize service history

No more spreadsheets or disorganized notes. Everything in one place.

## Quick Start

### Prerequisites
- Docker and Docker Compose
- Node.js 18+
- Composer

### Setup
```bash
# One command setup
composer sidequest:go
```

This will:
- Start all services (web, database, cache, email testing)
- Install dependencies
- Set up the database
- Build frontend assets

### Access
- **App**: http://localhost:8000
- **Email Testing**: http://localhost:8025

## Configuration

Set your database credentials in `.env`:
```bash
DOCKER_DB_DATABASE=sidequest_crm
DOCKER_DB_USERNAME=sidequest_crm
DOCKER_DB_PASSWORD=your_password_here
DOCKER_DB_ROOT_PASSWORD=your_root_password_here
```

Generate secure passwords:
```bash
composer sidequest:secrets
```

## Development Commands

```bash
composer sidequest:go      # Start everything
composer sidequest:up      # Start services
composer sidequest:down    # Stop services
composer sidequest:dev     # Frontend dev mode
```

## Tech Stack

- **Backend**: Laravel 11, PHP 8.2
- **Frontend**: Vue.js 3, Tailwind CSS
- **Database**: MySQL 8.0
- **Auth**: Laravel Jetstream
- **Multi-tenancy**: Database-per-tenant isolation

## Multi-Tenancy

Each business gets their own isolated workspace with separate database. Perfect for agencies managing multiple clients or SaaS deployment.

## License

MIT License
