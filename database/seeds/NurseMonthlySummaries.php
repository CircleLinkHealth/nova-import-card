<?php

use App\Call;
use Illuminate\Database\Seeder;

class NurseMonthlySummaries extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        /*
         * Hacking this to use for more than one month. Going to populate July, August and September
         */

        $dates = [
            '2016-08-01',
            '2016-09-01'
        ];

        foreach ($dates as $month) {

            $start_date = $month;
            $end_date = \Carbon\Carbon::parse($month)->lastOfMonth()->toDateString();

            $nurses = \App\NurseInfo::all();

            $this->command->info('Transferring Nurses for: '. $month);
            $this->command->line('');

            foreach ($nurses as $nurse) {

                $no_of_calls = Call::where('outbound_cpm_id', $nurse->user_id)
                    ->where('created_at', '>=', $start_date)
                    ->where('created_at', '<=', $end_date)->count();

                $no_of_successful_calls = Call::where('status', 'reached')
                    ->where('outbound_cpm_id', $nurse->user_id)
                    ->where('created_at', '<=', $end_date)
                    ->where('created_at', '>=', $start_date)->count();

                $time = \App\PageTimer::where('provider_id', $nurse->user_id)
                    ->where('created_at', '<=', $end_date)
                    ->where('created_at', '>=', $start_date)->sum('duration');

                $ccm_time = \App\Activity::where('provider_id', $nurse->user_id)
                    ->where('created_at', '<=', $end_date)
                    ->where('created_at', '>=', $start_date)->sum('duration');

                \App\NurseMonthlySummary::create([

                    'nurse_id' => $nurse->id,
                    'ccm_time' => $ccm_time,
                    'time' => $time,
                    'month_year' => $start_date,
                    'no_of_calls' => $no_of_calls,
                    'no_of_successful_calls' => $no_of_successful_calls

                ]);

                $this->command->info('Completed Nurse: ' . $nurse->user_id . '!');
                $this->command->line('');

            }

            $this->command->info($month . 'Fin.');

        }

        $this->command->info('All Done Here. Enjoy Your Shiny New Patient Statistics!');

    }

}
