<?php

use App\Call;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class PatientInfoLastContactDaySeeder extends Seeder
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

            $last_successful_call = Call::where('status','reached')->where(
                function ($q) use ($patient){
                    $q->where('outbound_cpm_id', $patient->user_id)
                        ->orWhere('inbound_cpm_id', $patient->user_id);
                })->orderBy('updated_at')->first();

            $this->command->info('Call: ' . $last_successful_call);


            if($last_successful_call){
                $patient->last_successful_contact_time = Carbon::parse($last_successful_call->updated_at)->format('Y-m-d');
                $patient->save();

                $this->command->info('Patient last day updated... ' . $patient->user_id .  ': ' . $patient->last_successful_contact_time );


            }
        }
    }
}