# SideQuest CRM Development Guide

This guide covers the development workflow for the SideQuest CRM system.

## 🚀 Quick Start

### One-Command Setup
```bash
composer sidequest:go
```

This single command will:
- ✅ Check prerequisites (Docker, ports, etc.)
- ✅ Set up environment files
- ✅ Start all Docker containers
- ✅ Wait for services to be ready
- ✅ Install PHP and Node.js dependencies
- ✅ Set up Laravel with Jetstream
- ✅ Build frontend assets
- ✅ Display success message with access URLs

### Access Your Application
- **Main App**: http://localhost:8000
- **Email Testing**: http://localhost:8025
- **MySQL Database**: localhost:3306
- **Redis Cache**: localhost:6379

## 🛠️ Development Commands

### Core Commands
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
```

### Manual Commands
```bash
# View logs
docker-compose logs -f

# View specific service logs
docker-compose logs -f app
docker-compose logs -f db
docker-compose logs -f redis

# Access container shell
docker-compose exec app bash
docker-compose exec db mysql -u root -p

# Run artisan commands
docker-compose exec app php artisan migrate
docker-compose exec app php artisan tinker

# Run npm commands
docker-compose exec app npm install
docker-compose exec app npm run build
docker-compose exec app npm run dev
```

## 🏗️ Architecture Overview

### Backend (Laravel)
- **Framework**: Laravel 12 with PHP 8.2
- **Database**: MySQL 8.0
- **Cache**: Redis
- **Authentication**: Laravel Jetstream
- **Multi-tenancy**: Spatie Laravel Multitenancy
- **API**: RESTful with proper resources

### Frontend (Vue.js)
- **Framework**: Vue.js 3 with Composition API
- **State Management**: Pinia
- **Router**: Vue Router 4
- **Build Tool**: Vite
- **Styling**: Tailwind CSS
- **UI Components**: Headless UI + Heroicons

### Development Stack
- **Containerization**: Docker & Docker Compose
- **Web Server**: Nginx
- **Email Testing**: MailHog
- **Database Management**: MySQL client included

## 📁 Project Structure

```
SideQuestCrm/
├── app/
│   ├── Contracts/           # Interface definitions
│   ├── Http/
│   │   ├── Controllers/Api/ # API controllers
│   │   ├── Requests/        # Form request validation
│   │   └── Resources/       # API resources
│   ├── Models/              # Eloquent models
│   ├── Repositories/        # Data access layer
│   └── Services/            # Business logic layer
├── database/
│   └── migrations/          # Database migrations
├── docker/                  # Docker configuration
├── resources/
│   └── js/
│       ├── components/      # Vue components
│       ├── router/          # Vue router config
│       ├── stores/          # Pinia stores
│       └── views/           # Vue page components
├── routes/
│   └── api.php             # API routes
└── scripts/                # Development scripts
```

## 🔧 Development Workflow

### 1. Starting Development
```bash
# First time setup
composer sidequest:go

# For subsequent development sessions
composer sidequest:up
composer sidequest:dev  # In a separate terminal
```

### 2. Making Changes

#### Backend Changes
- Edit PHP files in `app/` directory
- Changes are automatically reflected (no restart needed)
- Run migrations: `docker-compose exec app php artisan migrate`

#### Frontend Changes
- Edit Vue files in `resources/js/` directory
- Changes are hot-reloaded automatically
- Build for production: `docker-compose exec app npm run build`

#### Database Changes
- Create new migration: `docker-compose exec app php artisan make:migration create_table_name`
- Run migrations: `docker-compose exec app php artisan migrate`
- Rollback: `docker-compose exec app php artisan migrate:rollback`

### 3. Testing
```bash
# Run PHP tests
docker-compose exec app php artisan test

# Run specific test
docker-compose exec app php artisan test --filter=CustomerTest
```

### 4. Database Management
```bash
# Access MySQL
docker-compose exec db mysql -u root -p sidequest_crm

# Reset database
docker-compose exec app php artisan migrate:fresh --seed

# Create seeder
docker-compose exec app php artisan make:seeder CustomerSeeder
```

## 🐛 Troubleshooting

### Common Issues

#### Port Already in Use
```bash
# Check what's using the port
lsof -i :8000
lsof -i :3306

# Kill the process or change ports in docker-compose.yml
```

#### Docker Issues
```bash
# Restart Docker
# Then run:
composer sidequest:restart
```

#### Database Connection Issues
```bash
# Check if MySQL is running
docker-compose ps db

# Check MySQL logs
docker-compose logs db

# Restart database
docker-compose restart db
```

#### Frontend Build Issues
```bash
# Clear npm cache
docker-compose exec app npm cache clean --force

# Reinstall dependencies
docker-compose exec app rm -rf node_modules package-lock.json
docker-compose exec app npm install
```

### Reset Everything
```bash
# Stop and remove everything
composer sidequest:down
docker-compose down -v
docker system prune -f

# Start fresh
composer sidequest:go
```

## 📚 Additional Resources

### Laravel Documentation
- [Laravel 12.x Documentation](https://laravel.com/docs/12.x)
- [Laravel Jetstream](https://jetstream.laravel.com/)
- [Laravel Sanctum](https://laravel.com/docs/sanctum)

### Vue.js Documentation
- [Vue.js 3 Guide](https://vuejs.org/guide/)
- [Pinia Documentation](https://pinia.vuejs.org/)
- [Vue Router](https://router.vuejs.org/)

### Development Tools
- [Docker Documentation](https://docs.docker.com/)
- [Tailwind CSS](https://tailwindcss.com/docs)
- [Headless UI](https://headlessui.com/)

## 🤝 Contributing

1. Follow SOLID principles
2. Write tests for new features
3. Use proper commit messages
4. Update documentation as needed
5. Follow the established code style

## 📞 Support

For development issues:
1. Check the troubleshooting section
2. Review Docker logs: `docker-compose logs -f`
3. Check Laravel logs: `docker-compose exec app tail -f storage/logs/laravel.log`
4. Create an issue with detailed error information 
