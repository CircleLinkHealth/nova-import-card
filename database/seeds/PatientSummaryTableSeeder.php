<?php

use App\Call;
use Illuminate\Database\Seeder;

class PatientSummaryTableSeeder extends Seeder
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
            '2016-07-01',
            '2016-08-01',
            '2016-09-01'
        ];

        foreach ($dates as $month) {

            $start_date = $month;
            $end_date = \Carbon\Carbon::parse($month)->lastOfMonth()->toDateString();

            $patients = \App\PatientInfo::all();

            $this->command->info('Transferring Patients for: '. $month);
            $this->command->line('');

            foreach ($patients as $patient) {

//                $this->command->info('Processing Patient: ' . $patient->user_id . '...');
//                $this->command->line('');

                $no_of_calls = Call::where('outbound_cpm_id', $patient->user_id)
                    ->where(
                        function ($q) use ($patient) {
                            $q->where('outbound_cpm_id', $patient->user_id)
                                ->orWhere('inbound_cpm_id', $patient->user_id);
                        })
                    ->where('created_at', '>=', $start_date)
                    ->where('created_at', '<=', $end_date)->count();

                $no_of_successful_calls = Call::where('status', 'reached')->where(function ($q) use ($patient) {
                    $q->where('outbound_cpm_id', $patient->user_id)
                        ->orWhere('inbound_cpm_id', $patient->user_id);
                })
                    ->where('created_at', '<=', $end_date)
                    ->where('created_at', '>=', $start_date)->count();

                \App\PatientMonthlySummary::create([

                    'patient_info_id' => $patient->id,
                    'ccm_time' => ($patient->cur_month_activity_time == null) ? '0' : $patient->cur_month_activity_time,
                    'month_year' => $start_date,
                    'no_of_calls' => $no_of_calls,
                    'no_of_successful_calls' => $no_of_successful_calls

                ]);

//                $this->command->info('Completed Patient: ' . $patient->user_id . '!');
//                $this->command->line('');

                $this->command->info($month . 'Fin.');

            }

            $this->command->info('All Done Here. Enjoy Your Shiny New Patient Statistics!');

        }
    }
}
