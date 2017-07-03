<?php

namespace App\Console\Commands;

use App\Models\MedicalRecords\Ccda;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ProcessCcdaLGHMixup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fix:lgh';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $ccdas = Ccda::withTrashed()
            ->get(['id'])
            ->map(function ($ccda) {
                $job = (new \App\Jobs\ProcessCcdaLGHMixup($ccda))
                    ->onQueue('ccda-processor')
                    ->delay(Carbon::now()->addSeconds(20));

                dispatch($job);
            });
    }
}
