<?php

namespace App\Console\Commands;

use App\Activity;
use App\Patient;
use Carbon\Carbon;
use Illuminate\Console\Command;


class RecalculateCcmTime extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ccm_time:recalculate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Goes through activities for this month and recalculates CCM Time.';

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
        $acts = Activity::where('performed_at', '>=', Carbon::now()->startOfMonth())
            ->where('performed_at', '<=', Carbon::now()->endOfMonth())
            ->groupBy('patient_id')
            ->selectRaw('sum(duration) as total_duration, patient_id')
            ->pluck('total_duration', 'patient_id');

        foreach ($acts as $id => $ccmTime) {
            try {
                $info = Patient::updateOrCreate([
                    'user_id' => $id,
                ]);

                if ($info) {
                    $info->cur_month_activity_time = $ccmTime;
                    $info->save();
                }
            } catch (\Exception $e) {
                \Log::alert($e);
                $this->error(json_encode($e));
            }
        }

        $this->info('CCM Time recalculated!');
    }
}
