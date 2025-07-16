#!/bin/bash

# SideQuest CRM Deployment Validation Script

# Source .env file if it exists
if [ -f .env ]; then
    export $(grep -v '^#' .env | xargs)
fi

echo "🔍 SideQuest CRM Deployment Validation"
echo ""

# Function to check if variable is set
check_var() {
    local var_name="$1"
    local var_value="${!var_name}"

    if [ -n "$var_value" ]; then
        echo "✅ $var_name: [set]"
        return 0
    else
        echo "❌ $var_name: [missing]"
        return 1
    fi
}

# Required variables for deployment
REQUIRED_VARS=(
    "DOCKER_DB_DATABASE"
    "DOCKER_DB_USERNAME"
    "DOCKER_DB_PASSWORD"
    "DOCKER_DB_ROOT_PASSWORD"
    "APP_KEY"
)

# Optional but recommended variables
RECOMMENDED_VARS=(
    "DOCKER_WEB_PORT"
    "DOCKER_DB_PORT"
    "DOCKER_REDIS_PORT"
    "SESSION_ENCRYPTION_KEY"
    "SANCTUM_STATEFUL_DOMAINS"
)

echo "📋 Required Variables:"
echo "====================="

MISSING_REQUIRED=0
for var in "${REQUIRED_VARS[@]}"; do
    if ! check_var "$var"; then
        MISSING_REQUIRED=$((MISSING_REQUIRED + 1))
    fi
done

echo ""
echo "📋 Recommended Variables:"
echo "========================"

MISSING_RECOMMENDED=0
for var in "${RECOMMENDED_VARS[@]}"; do
    if ! check_var "$var"; then
        MISSING_RECOMMENDED=$((MISSING_RECOMMENDED + 1))
    fi
done

echo ""
echo "📊 Summary:"
echo "==========="

if [ $MISSING_REQUIRED -eq 0 ]; then
    echo "✅ All required variables are set"
    echo "🚀 Ready for deployment!"
else
    echo "❌ Missing $MISSING_REQUIRED required variable(s)"
    echo "⚠️  Deployment will fail"
fi

if [ $MISSING_RECOMMENDED -gt 0 ]; then
    echo "⚠️  Missing $MISSING_RECOMMENDED recommended variable(s)"
    echo "💡 Consider setting these for better security and configuration"
fi

echo ""
echo "💡 Commands:"
echo "============"
echo "• Generate secrets: composer sidequest:secrets"
echo "• Customize config: composer sidequest:customize"
echo "• Start setup: composer sidequest:go"

# Exit with error if required variables are missing
if [ $MISSING_REQUIRED -gt 0 ]; then
    exit 1
fi
