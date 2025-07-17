<?php

namespace App\Observers;

use App\Models\Job;
use Illuminate\Support\Facades\Log;

class JobObserver
{
    public function created(Job $job)
    {
        Log::info('Job created', ['id' => $job->id, 'title' => $job->title]);
        // Add notification or other side effects here
    }

    public function updated(Job $job)
    {
        Log::info('Job updated', ['id' => $job->id, 'title' => $job->title]);
    }

    public function deleted(Job $job)
    {
        Log::info('Job deleted', ['id' => $job->id, 'title' => $job->title]);
    }
}
