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

# Configure database settings
echo "🗄️ Configuring database settings..."
sed -i '' 's/DB_CONNECTION=sqlite/DB_CONNECTION=mysql/' .env
sed -i '' 's/# DB_HOST=127.0.0.1/DB_HOST=db/' .env

# Configure database settings from .env file
echo "🗄️ Configuring database settings from .env file..."

# Check if required database variables are set
if [ -z "$DOCKER_DB_DATABASE" ] || [ -z "$DOCKER_DB_USERNAME" ] || [ -z "$DOCKER_DB_PASSWORD" ]; then
    echo "❌ Error: Database credentials not found in .env file"
    echo "   Please ensure the following variables are set in your .env file:"
    echo "   - DOCKER_DB_DATABASE"
    echo "   - DOCKER_DB_USERNAME"
    echo "   - DOCKER_DB_PASSWORD"
    echo "   - DOCKER_DB_ROOT_PASSWORD"
    echo ""
    echo "   You can generate secure values using: composer sidequest:secrets"
    exit 1
fi

# Use values from .env file (no defaults for credentials)
DB_PORT="${DOCKER_DB_PORT:-3306}"
DB_DATABASE="$DOCKER_DB_DATABASE"
DB_USERNAME="$DOCKER_DB_USERNAME"
DB_PASSWORD="$DOCKER_DB_PASSWORD"

sed -i '' "s/# DB_PORT=3306/DB_PORT=$DB_PORT/" .env
sed -i '' "s/# DB_DATABASE=laravel/DB_DATABASE=$DB_DATABASE/" .env
sed -i '' "s/# DB_USERNAME=root/DB_USERNAME=$DB_USERNAME/" .env
sed -i '' "s/# DB_PASSWORD=/DB_PASSWORD=$DB_PASSWORD/" .env

echo "✅ Database configuration validated and applied"

# Configure Redis settings
echo "🔴 Configuring Redis settings..."
REDIS_HOST="${DOCKER_REDIS_HOST:-redis}"
REDIS_PORT="${DOCKER_REDIS_PORT:-6379}"

sed -i '' "s/REDIS_HOST=127.0.0.1/REDIS_HOST=$REDIS_HOST/" .env
sed -i '' "s/REDIS_PORT=6379/REDIS_PORT=$REDIS_PORT/" .env

# Configure Mail settings
echo "📧 Configuring Mail settings..."
MAIL_HOST="${DOCKER_MAIL_HOST:-mailhog}"
MAIL_PORT="${DOCKER_MAIL_PORT:-1025}"

sed -i '' "s/MAIL_HOST=127.0.0.1/MAIL_HOST=$MAIL_HOST/" .env
sed -i '' "s/MAIL_PORT=2525/MAIL_PORT=$MAIL_PORT/" .env

echo "✅ Environment settings configured"

# Validate all required environment variables
echo "🔍 Validating environment configuration..."

# Check for required Docker variables
REQUIRED_VARS=(
    "DOCKER_DB_DATABASE"
    "DOCKER_DB_USERNAME"
    "DOCKER_DB_PASSWORD"
    "DOCKER_DB_ROOT_PASSWORD"
)

MISSING_VARS=()
for var in "${REQUIRED_VARS[@]}"; do
    if [ -z "${!var}" ]; then
        MISSING_VARS+=("$var")
    fi
done

if [ ${#MISSING_VARS[@]} -ne 0 ]; then
    echo "❌ Error: Missing required environment variables:"
    for var in "${MISSING_VARS[@]}"; do
        echo "   - $var"
    done
    echo ""
    echo "💡 To generate secure values, run: composer sidequest:secrets"
    echo "💡 To customize configuration, run: composer sidequest:customize"
    exit 1
fi

echo "✅ All required environment variables are set"

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

# Clear config cache
echo "🧹 Clearing configuration cache..."
docker-compose exec -T app php artisan config:clear

# Run database migrations
echo "🗄️ Running database migrations..."
docker-compose exec -T app php artisan migrate --force

# Install Jetstream
echo "🔐 Installing Laravel Jetstream..."
docker-compose exec -T app php artisan jetstream:install livewire --teams --force

# Run migrations again for Jetstream
echo "🗄️ Running Jetstream migrations..."
docker-compose exec -T app php artisan migrate --force

# Seed database with test data
echo "🌱 Seeding database with test data..."
docker-compose exec -T app php artisan db:seed

# Build frontend assets
echo "🏗️ Building frontend assets..."
docker-compose exec -T app npm run build

# Start Vite dev server
echo "🚀 Starting Vite dev server..."
docker-compose exec -T app npm run dev &
echo "⏳ Waiting for Vite to start..."
sleep 5

echo ""
echo "🎉 Setup complete!"
echo ""
echo "📋 Next steps:"
echo "1. Access the application at: http://localhost:8000"
echo "2. Login with test user: admin@sidequest.com / password"
echo "3. Access MailHog at: http://localhost:8025"
echo "4. Vite dev server is running at: http://localhost:5173"
echo ""
echo "📚 For detailed setup instructions, see README.md"
