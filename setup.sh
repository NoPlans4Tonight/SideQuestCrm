#!/bin/bash

# SideQuest CRM Setup Script

echo "🚀 Setting up SideQuest CRM..."

# Check if Docker is running
if ! docker info > /dev/null 2>&1; then
    echo "❌ Docker is not running. Please start Docker and try again."
    exit 1
fi

# Check if .env file exists
if [ ! -f .env ]; then
    echo "📝 Creating .env file from .env.example..."
    cp .env.example .env
    echo "✅ .env file created"
else
    echo "✅ .env file already exists"
fi

# Start Docker services
echo "🐳 Starting Docker services..."
docker-compose up -d

# Wait for services to be ready
echo "⏳ Waiting for services to be ready..."
sleep 10

# Install PHP dependencies
echo "📦 Installing PHP dependencies..."
docker-compose exec -T app composer install --no-interaction

# Install Node.js dependencies
echo "📦 Installing Node.js dependencies..."
docker-compose exec -T app npm install

# Generate application key
echo "🔑 Generating application key..."
docker-compose exec -T app php artisan key:generate

# Run database migrations
echo "🗄️ Running database migrations..."
docker-compose exec -T app php artisan migrate --force

# Install Jetstream
echo "🔐 Installing Laravel Jetstream..."
docker-compose exec -T app php artisan jetstream:install livewire --teams --force

# Run migrations again for Jetstream
echo "🗄️ Running Jetstream migrations..."
docker-compose exec -T app php artisan migrate --force

# Build frontend assets
echo "🏗️ Building frontend assets..."
docker-compose exec -T app npm run build

echo ""
echo "🎉 Setup complete!"
echo ""
echo "📋 Next steps:"
echo "1. Create your first tenant using: docker-compose exec app php artisan tinker"
echo "2. Access the application at: http://localhost:8000"
echo "3. Access MailHog at: http://localhost:8025"
echo ""
echo "📚 For detailed setup instructions, see README.md"
