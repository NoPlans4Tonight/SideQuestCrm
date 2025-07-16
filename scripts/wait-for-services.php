<?php

/**
 * Wait for Services Script
 *
 * This script waits for all required services to be ready before proceeding.
 * It follows the Single Responsibility Principle by focusing only on service readiness.
 */

class ServiceWaiter
{
    private const MAX_ATTEMPTS = 60;
    private const ATTEMPT_DELAY = 2;

    public function wait(): void
    {
        echo "⏳ Waiting for services to be ready...\n";

        $services = [
            'MySQL' => $this->waitForMySQL(),
            'Redis' => $this->waitForRedis(),
            'Nginx' => $this->waitForNginx(),
        ];

        $allReady = true;
        foreach ($services as $service => $ready) {
            if ($ready) {
                echo "✅ {$service} is ready\n";
            } else {
                echo "❌ {$service} failed to start\n";
                $allReady = false;
            }
        }

        if (!$allReady) {
            throw new Exception("Some services failed to start");
        }

        echo "✅ All services are ready!\n";
    }

    private function waitForMySQL(): bool
    {
        $attempts = 0;
        while ($attempts < self::MAX_ATTEMPTS) {
            $command = "docker-compose exec -T db mysqladmin ping -h localhost --silent 2>&1";
            $output = [];
            $returnCode = 0;

            exec($command, $output, $returnCode);

            if ($returnCode === 0) {
                return true;
            }

            $attempts++;
            sleep(self::ATTEMPT_DELAY);
        }

        return false;
    }

    private function waitForRedis(): bool
    {
        $attempts = 0;
        while ($attempts < self::MAX_ATTEMPTS) {
            $command = "docker-compose exec -T redis redis-cli ping 2>&1";
            $output = [];
            $returnCode = 0;

            exec($command, $output, $returnCode);

            if ($returnCode === 0 && in_array('PONG', $output)) {
                return true;
            }

            $attempts++;
            sleep(self::ATTEMPT_DELAY);
        }

        return false;
    }

    private function waitForNginx(): bool
    {
        $attempts = 0;
        while ($attempts < self::MAX_ATTEMPTS) {
            $connection = @fsockopen('localhost', 8000, $errno, $errstr, 1);
            if (is_resource($connection)) {
                fclose($connection);
                return true;
            }

            $attempts++;
            sleep(self::ATTEMPT_DELAY);
        }

        return false;
    }
}

// Run the waiter
$waiter = new ServiceWaiter();
$waiter->wait();
