<?php

/**
 * SideQuest CRM Development Environment Setup Script
 *
 * This script orchestrates the complete setup of the SideQuest CRM development environment.
 * It follows the Single Responsibility Principle by delegating specific tasks to helper functions.
 */

class SideQuestGo
{
    private const REQUIRED_PORTS = [8000, 3306, 6379, 1025, 8025];
    private const SERVICES = ['app', 'webserver', 'db', 'redis', 'mailhog'];

    public function run(): void
    {
        $this->displayBanner();

        try {
            $this->checkPrerequisites();
            $this->setupEnvironment();
            $this->startServices();
            $this->waitForServices();
            $this->installDependencies();
            $this->setupLaravel();
            $this->buildFrontend();
            $this->displaySuccess();
        } catch (Exception $e) {
            $this->displayError($e->getMessage());
            exit(1);
        }
    }

    private function displayBanner(): void
    {
        echo "\n";
        echo "🚀 SideQuest CRM Development Environment Setup\n";
        echo "==============================================\n";
        echo "\n";
    }

    private function checkPrerequisites(): void
    {
        echo "📋 Checking prerequisites...\n";

        // Check if Docker is running
        if (!$this->isDockerRunning()) {
            throw new Exception("❌ Docker is not running. Please start Docker and try again.");
        }

        // Check if Docker Compose is available
        if (!$this->isDockerComposeAvailable()) {
            throw new Exception("❌ Docker Compose is not available. Please install Docker Compose.");
        }

        // Check if required ports are available
        $this->checkPortAvailability();

        echo "✅ Prerequisites check passed\n\n";
    }

    private function setupEnvironment(): void
    {
        echo "🔧 Setting up environment...\n";

        // Create .env file if it doesn't exist
        if (!file_exists('.env')) {
            if (file_exists('.env.example')) {
                copy('.env.example', '.env');
                echo "✅ Created .env file from .env.example\n";
            } else {
                throw new Exception("❌ .env.example file not found. Please create it first.");
            }
        } else {
            echo "✅ .env file already exists\n";
        }

        echo "✅ Environment setup complete\n\n";
    }

    private function startServices(): void
    {
        echo "🐳 Starting Docker services...\n";

        $command = "docker-compose up -d";
        $output = [];
        $returnCode = 0;

        exec($command . " 2>&1", $output, $returnCode);

        if ($returnCode !== 0) {
            throw new Exception("❌ Failed to start Docker services: " . implode("\n", $output));
        }

        echo "✅ Docker services started\n\n";
    }

    private function waitForServices(): void
    {
        echo "⏳ Waiting for services to be ready...\n";

        $maxAttempts = 30;
        $attempt = 0;

        while ($attempt < $maxAttempts) {
            if ($this->areServicesReady()) {
                echo "✅ All services are ready\n\n";
                return;
            }

            $attempt++;
            echo "   Attempt {$attempt}/{$maxAttempts}...\n";
            sleep(2);
        }

        throw new Exception("❌ Services failed to start within the expected time.");
    }

    private function installDependencies(): void
    {
        echo "📦 Installing dependencies...\n";

        // Install PHP dependencies
        echo "   Installing PHP dependencies...\n";
        $this->runDockerCommand("composer install --no-interaction");

        // Install Node.js dependencies
        echo "   Installing Node.js dependencies...\n";
        $this->runDockerCommand("npm install");

        echo "✅ Dependencies installed\n\n";
    }

    private function setupLaravel(): void
    {
        echo "🔐 Setting up Laravel...\n";

        // Generate application key
        echo "   Generating application key...\n";
        $this->runDockerCommand("php artisan key:generate --force");

        // Install Jetstream (no --force)
        // echo "   Installing Laravel Jetstream...\n";
        // $this->runDockerCommand("php artisan jetstream:install livewire --teams");

        // Run migrations after Jetstream install
        echo "   Running database migrations...\n";
        $this->runDockerCommand("php artisan migrate --force");

        echo "✅ Laravel setup complete\n\n";
    }

    private function buildFrontend(): void
    {
        echo "🏗️ Building frontend assets for development...\n";

        // Build assets for development (with source maps and debugging)
        $this->runDockerCommand("npm run build");

        echo "✅ Frontend assets built for development\n";
        echo "   🔧 Starting Vite dev server with hot reload...\n";

        // Start Vite dev server in background
        $this->startViteDevServer();

        echo "✅ Vite dev server started\n";
        echo "   🔧 Vue DevTools available at: http://localhost:5173\n\n";
    }

    private function displaySuccess(): void
    {
        echo "🎉 Setup complete!\n";
        echo "==================\n";
        echo "\n";
        echo "📋 Your SideQuest CRM is ready:\n";
        echo "   🌐 Main Application: http://localhost:8000\n";
        echo "   🔧 Vue DevTools: http://localhost:5173 (with hot reload)\n";
        echo "   📧 MailHog (Email Testing): http://localhost:8025\n";
        echo "   🗄️ MySQL Database: localhost:3306\n";
        echo "   🔴 Redis Cache: localhost:6379\n";
        echo "\n";
        echo "📚 Next steps:\n";
        echo "   1. Create your first tenant using: docker-compose exec app php artisan tinker\n";
        echo "   2. Access the application at: http://localhost:8000\n";
        echo "   3. Open Vue DevTools in Chrome/Firefox for debugging\n";
        echo "   4. Check the README.md for detailed setup instructions\n";
        echo "\n";
        echo "🛠️ Useful commands:\n";
        echo "   composer sidequest:down    - Stop all services\n";
        echo "   composer sidequest:restart - Restart all services\n";
        echo "   composer sidequest:dev     - Restart Vue dev server (if needed)\n";
        echo "   composer sidequest:build   - Build for production deployment\n";
        echo "   docker-compose logs -f     - View live logs\n";
        echo "\n";
    }

    private function displayError(string $message): void
    {
        echo "\n❌ Error: {$message}\n";
        echo "\n💡 Troubleshooting tips:\n";
        echo "   - Make sure Docker is running\n";
        echo "   - Check if ports 8000, 3306, 6379 are available\n";
        echo "   - Try running: composer sidequest:down && composer sidequest:up\n";
        echo "\n";
    }

    private function isDockerRunning(): bool
    {
        $output = [];
        $returnCode = 0;
        exec("docker info 2>&1", $output, $returnCode);
        return $returnCode === 0;
    }

    private function isDockerComposeAvailable(): bool
    {
        $output = [];
        $returnCode = 0;
        exec("docker-compose --version 2>&1", $output, $returnCode);
        return $returnCode === 0;
    }

    private function checkPortAvailability(): void
    {
        foreach (self::REQUIRED_PORTS as $port) {
            if ($this->isPortInUse($port)) {
                throw new Exception("❌ Port {$port} is already in use. Please free up the port and try again.");
            }
        }
    }

    private function isPortInUse(int $port): bool
    {
        $connection = @fsockopen('localhost', $port, $errno, $errstr, 1);
        if (is_resource($connection)) {
            fclose($connection);
            return true;
        }
        return false;
    }

            private function areServicesReady(): bool
    {
        // Check if MySQL is ready
        $mysqlReady = $this->runDockerCommandFromContainer("db", "mysqladmin ping -h localhost --silent", true);

        // Check if Redis is ready
        $redisReady = $this->runDockerCommandFromContainer("redis", "redis-cli ping", true);

        return $mysqlReady && $redisReady;
    }

    private function runDockerCommandFromContainer(string $container, string $command, bool $silent = false): bool
    {
        $fullCommand = "docker-compose exec -T {$container} {$command}";

        if (!$silent) {
            echo "   Running: {$command}\n";
        }

        $output = [];
        $returnCode = 0;

        exec($fullCommand . " 2>&1", $output, $returnCode);

        if (!$silent && $returnCode !== 0) {
            throw new Exception("Command failed: " . implode("\n", $output));
        }

        return $returnCode === 0;
    }

    private function runDockerCommand(string $command, bool $silent = false): bool
    {
        $fullCommand = "docker-compose exec -T app {$command}";

        if (!$silent) {
            echo "   Running: {$command}\n";
        }

        $output = [];
        $returnCode = 0;

        exec($fullCommand . " 2>&1", $output, $returnCode);

        if (!$silent && $returnCode !== 0) {
            throw new Exception("Command failed: " . implode("\n", $output));
        }

        return $returnCode === 0;
    }

    private function startViteDevServer(): void
    {
        // Start Vite dev server in background
        $command = "docker-compose exec -d app npm run dev";

        $output = [];
        $returnCode = 0;

        exec($command . " 2>&1", $output, $returnCode);

        // Give it a moment to start
        sleep(3);

        // Check if it's running
        if (!$this->isViteDevServerRunning()) {
            echo "   ⚠️  Vite dev server may not have started properly\n";
            echo "   💡 You can manually start it with: composer sidequest:dev\n";
        }
    }

    private function isViteDevServerRunning(): bool
    {
        $connection = @fsockopen('localhost', 5173, $errno, $errstr, 2);
        if (is_resource($connection)) {
            fclose($connection);
            return true;
        }
        return false;
    }
}

// Run the script
$script = new SideQuestGo();
$script->run();
