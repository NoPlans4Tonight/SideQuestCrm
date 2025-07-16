#!/bin/bash

# SideQuest CRM Frontend Development Script
# This script runs the frontend in development mode with hot reloading

echo "🎨 Starting SideQuest CRM Frontend Development Mode"
echo "=================================================="
echo ""

# Check if containers are running
if ! docker-compose ps | grep -q "Up"; then
    echo "❌ Docker containers are not running. Please run 'composer sidequest:go' first."
    exit 1
fi

echo "✅ Docker containers are running"
echo ""

# Start frontend development server
echo "🚀 Starting Vite development server..."
echo "   This will watch for changes and automatically rebuild assets"
echo "   Access your app at: http://localhost:8000"
echo "   Vue DevTools available at: http://localhost:5173"
echo "   Make sure you have Vue DevTools extension installed in your browser"
echo ""

# Run npm run dev in the container
docker-compose exec -T app npm run dev
