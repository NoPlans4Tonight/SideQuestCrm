#!/bin/bash

# SideQuest CRM Docker Customization Script

echo "🔧 SideQuest CRM Docker Configuration Customizer"
echo ""

# Function to prompt for input with default value
prompt_with_default() {
    local prompt="$1"
    local default="$2"
    local var_name="$3"

    echo -n "$prompt [$default]: "
    read -r input

    if [ -z "$input" ]; then
        input="$default"
    fi

    # Update .env file
    if grep -q "^$var_name=" .env; then
        sed -i '' "s/^$var_name=.*/$var_name=$input/" .env
    else
        echo "$var_name=$input" >> .env
    fi

    echo "✅ Set $var_name=$input"
}

echo "📋 Current Docker Configuration:"
echo "  Web Server Port: ${DOCKER_WEB_PORT:-8000}"
echo "  Database Port: ${DOCKER_DB_PORT:-3306}"
echo "  Redis Port: ${DOCKER_REDIS_PORT:-6379}"
echo "  MailHog SMTP Port: ${DOCKER_MAILHOG_SMTP_PORT:-1025}"
echo "  MailHog UI Port: ${DOCKER_MAILHOG_UI_PORT:-8025}"
echo ""

echo "🎯 Customize Docker Configuration (press Enter to keep current values):"
echo ""

# Prompt for port customizations
prompt_with_default "Web Server Port" "${DOCKER_WEB_PORT:-8000}" "DOCKER_WEB_PORT"
prompt_with_default "Database Port" "${DOCKER_DB_PORT:-3306}" "DOCKER_DB_PORT"
prompt_with_default "Redis Port" "${DOCKER_REDIS_PORT:-6379}" "DOCKER_REDIS_PORT"
prompt_with_default "MailHog SMTP Port" "${DOCKER_MAILHOG_SMTP_PORT:-1025}" "DOCKER_MAILHOG_SMTP_PORT"
prompt_with_default "MailHog UI Port" "${DOCKER_MAILHOG_UI_PORT:-8025}" "DOCKER_MAILHOG_UI_PORT"

echo ""
echo "🗄️ Database Configuration:"
prompt_with_default "Database Name" "${DOCKER_DB_DATABASE}" "DOCKER_DB_DATABASE"
prompt_with_default "Database Username" "${DOCKER_DB_USERNAME}" "DOCKER_DB_USERNAME"
prompt_with_default "Database Password" "${DOCKER_DB_PASSWORD}" "DOCKER_DB_PASSWORD"
prompt_with_default "Database Root Password" "${DOCKER_DB_ROOT_PASSWORD}" "DOCKER_DB_ROOT_PASSWORD"

echo ""
echo "🔄 Restarting Docker services with new configuration..."
docker-compose down
docker-compose up -d

echo ""
echo "✅ Docker configuration updated and services restarted!"
echo ""
echo "📋 New configuration:"
echo "  Web Server: http://localhost:${DOCKER_WEB_PORT:-8000}"
echo "  Database: localhost:${DOCKER_DB_PORT:-3306}"
echo "  Redis: localhost:${DOCKER_REDIS_PORT:-6379}"
echo "  MailHog UI: http://localhost:${DOCKER_MAILHOG_UI_PORT:-8025}"
echo ""
echo "💡 To apply these changes to your Laravel app, run:"
echo "   docker-compose exec app php artisan config:clear"
