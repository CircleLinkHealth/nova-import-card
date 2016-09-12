<?php

use App\User;
use App\CareItemUserValue;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Seeder;

class S20160910ReconcileCallAttempts extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        $patients = User::with('roles')
            ->with('patientInfo')
            ->whereHas('roles', function($q) {
                $q->where(function ($query) {
                    $query->where('name', 'participant');
                });
            })
            ->orderBy( 'ID', 'desc' )
            ->get();

        foreach($patients as $patient) {
            if($patient->patientInfo) {
                // count number of missed calls
                $notes= $patient->notes()->with('call')->has('call')->orderBy( 'performed_at', 'desc' )->get();
                if($notes->count() >= 1) {
                    $this->command->info('Patient ' . $patient->ID . ': ' . $notes->count());

                    $n = 0;
                    foreach($notes as $note) {
                        $this->command->info('Note ' . $note->id . ': [' . $note->performed_at . '] :: '.$note->call->status);
                        if ($note->call->status == 'reached') {
                            $patient->patientInfo->no_call_attempts_since_last_success = $n;
                            $patient->patientInfo->save();
                            $this->command->info('Updated PatientInfo ' . $patient->ID . ' no_call_attempts_since_last_success: ' . $patient->patientInfo->no_call_attempts_since_last_success);
                            break;
                        }
                        $n++;
                        if($n == $notes->count()) {
                            $patient->patientInfo->no_call_attempts_since_last_success = $n;
                            $patient->patientInfo->save();
                            $this->command->info('Updated PatientInfo ' . $patient->ID . ' no_call_attempts_since_last_success: ' . $patient->patientInfo->no_call_attempts_since_last_success);
                        }
                    }
                    $this->command->info('Patient ' . $patient->ID . ': DONE');
                }
            }
        }
    }
}
