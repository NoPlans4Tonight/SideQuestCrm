# 🚀 Laravel Cloud Deployment Guide for SideQuest CRM

## Prerequisites

1. **Laravel Cloud Account** - Sign up at [cloud.laravel.com](https://cloud.laravel.com)
2. **Git Repository** - Your code must be in a Git repository (GitHub, GitLab, etc.)
3. **Production Build** - Run `composer sidequest:build` before deployment

## Step 1: Prepare Your Repository

### 1.1 Build for Production
```bash
composer sidequest:build
```

### 1.2 Commit Your Changes
```bash
git add .
git commit -m "🔧 Prepare for Laravel Cloud deployment"
git push origin main
```

## Step 2: Laravel Cloud Setup

### 2.1 Create New Project
1. Go to [cloud.laravel.com](https://cloud.laravel.com)
2. Click "Create New Project"
3. Connect your Git repository
4. Select your SideQuest CRM repository

### 2.2 Configure Project Settings
- **Project Name**: `sidequest-crm`
- **PHP Version**: `8.2`
- **Node.js Version**: `18` (or latest LTS)

## Step 3: Environment Variables

Set these environment variables in Laravel Cloud dashboard:

### Required Variables
```bash
APP_NAME="SideQuest CRM"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.laravel.cloud

# Database (Laravel Cloud will provide these automatically)
DB_CONNECTION=mysql
DB_HOST=${DB_HOST}
DB_PORT=3306
DB_DATABASE=${DB_DATABASE}
DB_USERNAME=${DB_USERNAME}
DB_PASSWORD=${DB_PASSWORD}

# Redis (Laravel Cloud will provide these automatically)
REDIS_HOST=${REDIS_HOST}
REDIS_PASSWORD=${REDIS_PASSWORD}
REDIS_PORT=6379

# Cache, Queue, and Session (IMPORTANT: Use Redis, not database)
CACHE_DRIVER=redis
CACHE_STORE=redis
QUEUE_CONNECTION=redis
SESSION_DRIVER=redis
SESSION_LIFETIME=120

# Mail Configuration
MAIL_MAILER=smtp
MAIL_HOST=mail.laravel.cloud
MAIL_PORT=587
MAIL_USERNAME=${MAIL_USERNAME}
MAIL_PASSWORD=${MAIL_PASSWORD}
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@yourdomain.com"
MAIL_FROM_NAME="SideQuest CRM"

# Sanctum Configuration
SANCTUM_STATEFUL_DOMAINS=your-domain.laravel.cloud

# Test Users (for initial setup - REMOVE AFTER SETUP)
TEST_ADMIN_PASSWORD=your_secure_password_here
TEST_USER_PASSWORD=your_secure_password_here
```

### Optional Variables
```bash
# AWS S3 (if using for file storage)
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=

# Logging
LOG_CHANNEL=stack
LOG_LEVEL=error

# Vite
VITE_APP_NAME="SideQuest CRM"
```

## Step 4: Build Configuration

Laravel Cloud will use the `laravel-cloud.json` configuration file to build your application.

### Build Steps (Automatic)
1. `composer install --no-dev --optimize-autoloader`
2. `npm ci`
3. `npm run build`
4. `php artisan config:cache`
5. `php artisan route:cache`
6. `php artisan view:cache`

### Deploy Steps (Automatic)
1. `php artisan migrate --force`
2. `php artisan storage:link`

## Step 5: Database Setup

### 5.1 Create Database
1. In Laravel Cloud dashboard, go to "Databases"
2. Create a new MySQL 8.0 database
3. Note the connection details

### 5.2 Run Migrations
Migrations will run automatically during deployment, but you can also run them manually:
```bash
php artisan migrate --force
```

### 5.3 Seed Initial Data (Optional)
```bash
php artisan db:seed --class=TestUserSeeder
```

## Step 6: Domain Configuration

### 6.1 Custom Domain (Optional)
1. In Laravel Cloud dashboard, go to "Domains"
2. Add your custom domain
3. Update DNS records as instructed
4. Update `APP_URL` and `SANCTUM_STATEFUL_DOMAINS` environment variables

### 6.2 SSL Certificate
Laravel Cloud automatically provides SSL certificates for all domains.

## Step 7: Monitoring and Logs

### 7.1 Application Logs
- View logs in Laravel Cloud dashboard
- Set up log forwarding to external services if needed

### 7.2 Performance Monitoring
- Laravel Cloud provides built-in performance monitoring
- Set up alerts for errors and performance issues

## Step 8: Post-Deployment

### 8.1 Verify Deployment
1. Visit your application URL
2. Check that all features work correctly
3. Verify database connections
4. Test user authentication

### 8.2 Security Checklist
- [ ] Remove test user passwords from environment variables
- [ ] Verify HTTPS is working
- [ ] Check that debug mode is disabled
- [ ] Verify proper error handling
- [ ] Test user registration/login

### 8.3 Performance Optimization
- [ ] Verify asset caching is working
- [ ] Check Redis connections
- [ ] Monitor application performance
- [ ] Set up queue workers if needed

## Troubleshooting

### Common Issues

#### 1. Build Failures
- Check that all dependencies are in `composer.json`
- Verify Node.js version compatibility
- Check for syntax errors in your code

#### 2. Database Connection Issues
- Verify database credentials in environment variables
- Check that database is accessible from Laravel Cloud
- Ensure migrations can run successfully
- **IMPORTANT**: Make sure `DB_CONNECTION=mysql` is set (not sqlite)

#### 3. Cache/Queue/Session Issues
- **CRITICAL**: Set `CACHE_DRIVER=redis`, `QUEUE_CONNECTION=redis`, `SESSION_DRIVER=redis`
- Do NOT use 'database' for these in production
- Ensure Redis credentials are properly configured

#### 4. Asset Loading Issues
- Verify Vite build completed successfully
- Check that `npm run build` generated assets
- Ensure `APP_URL` is set correctly

#### 4. Authentication Issues
- Verify Sanctum configuration
- Check CORS settings
- Ensure session configuration is correct

### Support
- Laravel Cloud Documentation: [docs.laravel.com/cloud](https://docs.laravel.com/cloud)
- Laravel Cloud Support: Available in dashboard
- Community: Laravel Discord and forums

## Cost Optimization

### Laravel Cloud Pricing
- **Hobby**: $9/month (1 project, 512MB RAM)
- **Developer**: $19/month (3 projects, 1GB RAM)
- **Team**: $39/month (10 projects, 2GB RAM)

### Recommendations
- Start with Hobby plan for testing
- Upgrade to Developer plan for production
- Monitor resource usage and optimize as needed

## Next Steps

1. **Set up CI/CD** - Connect your Git repository for automatic deployments
2. **Configure backups** - Set up database and file backups
3. **Set up monitoring** - Configure alerts and performance monitoring
4. **Scale as needed** - Upgrade plans as your application grows

---

**Need Help?**
- Laravel Cloud Documentation: [docs.laravel.com/cloud](https://docs.laravel.com/cloud)
- Laravel Community: [laravel.com/discord](https://laravel.com/discord)
- Support: Available in Laravel Cloud dashboard 
