<?php

use Illuminate\Database\Seeder;

class PatientSummaryTableSeederJuly extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $patients = \App\PatientInfo::all();
        
        foreach ($patients as $patient){

            $this->command->info('Transferring Patient: ' . $patient->user_id .  '...');
            $this->command->line('');

            $no_of_calls = \App\Call::where('outbound_cpm_id', $patient->user_id)
                                        ->orWhere('inbound_cpm_id', $patient->user_id)
                                        ->where('created_at', '<=' , '2016-07-31')
                                        ->where('created_at', '>=' , '2016-07-01')->count();

            $no_of_successful_calls = \App\Call::where('status','reached')->where(function ($q) use ($patient){
                                             $q->where('outbound_cpm_id', $patient->user_id)
                                              ->orWhere('inbound_cpm_id', $patient->user_id);})
                                        ->where('created_at', '<=' , '2016-07-31')
                                        ->where('created_at', '>=' , '2016-07-01')->count();
            
            \App\PatientMonthlySummary::create([
                
                'patient_info_id' => $patient->id,
                'ccm_time' => ($patient->cur_month_activity_time == null ) ? '0' : $patient->cur_month_activity_time,
                'month_year' => '2016-07-01',
                'no_of_calls' => $no_of_calls,
                'no_of_successful_calls' => $no_of_successful_calls
                
            ]);

            $this->command->info('Completed Patient: ' . $patient->user_id .  '!');
            $this->command->line('');
        }

        $this->command->info('Fin.');

    }
}
