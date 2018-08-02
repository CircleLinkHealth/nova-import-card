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
        Patient::withTrashed()
            ->update([
                'cur_month_activity_time' => '0',
            ]);

        AppConfig::updateOrCreate([
            'config_key'   => 'reset_cur_month_activity_time',
            'config_value' => Carbon::now(),
        ]);

        $this->info('CCM Time reset.');
    }
}
