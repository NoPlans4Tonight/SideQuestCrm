#!/bin/bash

# SideQuest CRM Secret Generation Script

echo "🔐 Generating secure secrets for SideQuest CRM..."
echo ""

# Function to generate random string
generate_random_string() {
    local length=$1
    openssl rand -base64 $((length * 3 / 4)) | tr -d "=+/" | cut -c1-${length}
}

# Function to update or add environment variable
update_env_var() {
    local var_name="$1"
    local var_value="$2"

    if grep -q "^$var_name=" .env; then
        # Variable exists, update it
        sed -i '' "s/^$var_name=.*/$var_name=$var_value/" .env
        echo "✅ Updated $var_name"
    else
        # Variable doesn't exist, add it
        echo "$var_name=$var_value" >> .env
        echo "✅ Added $var_name"
    fi
}

# Generate secure secrets
echo "🔑 Generating application secrets..."

# Check if database credentials already exist
if [ -n "$DOCKER_DB_PASSWORD" ] && [ -n "$DOCKER_DB_ROOT_PASSWORD" ]; then
    echo "✅ Database credentials already exist in .env file"
    echo "   DOCKER_DB_PASSWORD: [set]"
    echo "   DOCKER_DB_ROOT_PASSWORD: [set]"
else
    echo "🔐 Generating new database credentials..."

    # Generate database password (16 characters)
    DB_PASSWORD=$(generate_random_string 16)
    update_env_var "DOCKER_DB_PASSWORD" "$DB_PASSWORD"
    update_env_var "DB_PASSWORD" "$DB_PASSWORD"

    # Generate database root password (16 characters)
    DB_ROOT_PASSWORD=$(generate_random_string 16)
    update_env_var "DOCKER_DB_ROOT_PASSWORD" "$DB_ROOT_PASSWORD"

    echo "✅ Generated new database credentials"
fi

# Generate application key (if not already set)
if ! grep -q "^APP_KEY=base64:" .env; then
    echo "🔑 Generating Laravel application key..."
    docker-compose exec -T app php artisan key:generate --force
fi

# Generate session encryption key (if not already set)
if ! grep -q "^SESSION_ENCRYPTION_KEY=" .env; then
    SESSION_KEY=$(generate_random_string 32)
    update_env_var "SESSION_ENCRYPTION_KEY" "$SESSION_KEY"
fi

# Generate API tokens secret (if not already set)
if ! grep -q "^SANCTUM_STATEFUL_DOMAINS=" .env; then
    update_env_var "SANCTUM_STATEFUL_DOMAINS" "localhost,127.0.0.1,localhost:8000"
fi

echo ""
echo "✅ All secrets generated successfully!"
echo ""
echo "📋 Generated secrets:"
echo "  Database Password: $DB_PASSWORD"
echo "  Database Root Password: $DB_ROOT_PASSWORD"
echo ""
echo "💡 These secrets are now stored in your .env file"
echo "   Keep this file secure and never commit it to version control!"
