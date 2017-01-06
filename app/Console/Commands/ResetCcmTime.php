<?php

namespace App\Console\Commands;

use App\AppConfig;
use App\Patient;
use Carbon\Carbon;
use Illuminate\Console\Command;

class ResetCcmTime extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reset:ccm_time';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Resets CCM time for all patients.';

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
        $appConfigs = AppConfig::all();

        $lastReset = $appConfigs->where('config_key', 'cur_month_ccm_time_last_reset')->first();

        Patient::withTrashed()
            ->update([
                'cur_month_activity_time' => '0',
            ]);

        $lastReset->config_value = Carbon::now();
        $lastReset->save();

        $this->info('CCM Time reset.');
    }
}
