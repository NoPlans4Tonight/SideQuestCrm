<?php

/**
 * SideQuest CRM Production Build Script
 *
 * This script builds the application for production deployment.
 * It optimizes assets, minifies code, and prepares for cloud deployment.
 */

class SideQuestProductionBuild
{
    public function run(): void
    {
        $this->displayBanner();

        try {
            $this->checkPrerequisites();
            $this->buildProductionAssets();
            $this->optimizeLaravel();
            $this->displaySuccess();
        } catch (Exception $e) {
            $this->displayError($e->getMessage());
            exit(1);
        }
    }

    private function displayBanner(): void
    {
        echo "\n";
        echo "🏭 SideQuest CRM Production Build\n";
        echo "=================================\n";
        echo "\n";
    }

    private function checkPrerequisites(): void
    {
        echo "📋 Checking prerequisites...\n";

        // Check if Docker is running
        if (!$this->isDockerRunning()) {
            throw new Exception("❌ Docker is not running. Please start Docker and try again.");
        }

        // Check if containers are running
        if (!$this->areContainersRunning()) {
            throw new Exception("❌ Docker containers are not running. Please run 'composer sidequest:go' first.");
        }

        echo "✅ Prerequisites check passed\n\n";
    }

    private function buildProductionAssets(): void
    {
        echo "🏗️ Building production assets...\n";

        // Install dependencies if needed
        echo "   Installing dependencies...\n";
        $this->runDockerCommand("npm install");

        // Build for production (optimized, minified)
        echo "   Building optimized assets...\n";
        $this->runDockerCommand("npm run build");

        echo "✅ Production assets built\n\n";
    }

    private function optimizeLaravel(): void
    {
        echo "⚡ Optimizing Laravel for production...\n";

        // Clear and cache config
        echo "   Caching configuration...\n";
        $this->runDockerCommand("php artisan config:cache");

        // Clear and cache routes
        echo "   Caching routes...\n";
        $this->runDockerCommand("php artisan route:cache");

        // Clear and cache views
        echo "   Caching views...\n";
        $this->runDockerCommand("php artisan view:cache");

        // Optimize autoloader
        echo "   Optimizing autoloader...\n";
        $this->runDockerCommand("composer install --optimize-autoloader --no-dev");

        echo "✅ Laravel optimized for production\n\n";
    }

    private function displaySuccess(): void
    {
        echo "🎉 Production build complete!\n";
        echo "=============================\n";
        echo "\n";
        echo "📋 Your application is ready for deployment:\n";
        echo "   ✅ Assets optimized and minified\n";
        echo "   ✅ Laravel configuration cached\n";
        echo "   ✅ Routes and views cached\n";
        echo "   ✅ Autoloader optimized\n";
        echo "\n";
        echo "🚀 Ready for deployment to your chosen cloud service!\n";
        echo "\n";
        echo "📚 Next steps:\n";
        echo "   1. Choose your cloud provider (AWS, DigitalOcean, Vercel, etc.)\n";
        echo "   2. Set up your deployment pipeline\n";
        echo "   3. Configure environment variables for production\n";
        echo "   4. Deploy your application\n";
        echo "\n";
    }

    private function displayError(string $message): void
    {
        echo "\n❌ Error: {$message}\n";
        echo "\n💡 Troubleshooting tips:\n";
        echo "   - Make sure Docker is running\n";
        echo "   - Ensure containers are running with 'composer sidequest:go'\n";
        echo "   - Check if all dependencies are installed\n";
        echo "\n";
    }

    private function isDockerRunning(): bool
    {
        $output = [];
        $returnCode = 0;
        exec("docker info 2>&1", $output, $returnCode);
        return $returnCode === 0;
    }

    private function areContainersRunning(): bool
    {
        $output = [];
        $returnCode = 0;
        exec("docker-compose ps | grep -q 'Up'", $output, $returnCode);
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
}

// Run the script
$script = new SideQuestProductionBuild();
$script->run();
