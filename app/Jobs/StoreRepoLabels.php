<?php

namespace App\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class StoreRepoLabels implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public $repo, public $labels)
    {

    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->repo->labels()->createMany($this->labels);
    }
}
