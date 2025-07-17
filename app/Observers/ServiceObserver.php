<?php

namespace App\Observers;

use App\Models\Service;
use Illuminate\Support\Facades\Log;

class ServiceObserver
{
    public function created(Service $service)
    {
        Log::info('Service created', ['id' => $service->id, 'name' => $service->name]);
        // Add notification or other side effects here
    }

    public function updated(Service $service)
    {
        Log::info('Service updated', ['id' => $service->id, 'name' => $service->name]);
    }

    public function deleted(Service $service)
    {
        Log::info('Service deleted', ['id' => $service->id, 'name' => $service->name]);
    }
}
