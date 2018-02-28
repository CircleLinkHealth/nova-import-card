<?php

namespace App\Console\Commands\Athena;

use App\Appointment;
use App\Services\AthenaAPI\Calls;
use App\User;
use Illuminate\Console\Command;

class UpdatePracticeAppointments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'athena:updateAppointments';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retrieves all appointments of a Patient from Athena and syncs with the database';

    private $api;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Calls $api)
    {
        parent::__construct();

        $this->api = $api;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        //get appointments for user in ehr

        $patients = User::with([
            'patientInfo',
            'ehrInfo'         => function ($e) {
                $e->where('ehr_id', 2);
            },
            'primaryPractice' => function ($practice) {
                $practice->whereHas('ehr', function ($q) {
                    $q->where('name', '=', 'Athena')
                      ->whereNotNull('external_id');
                });
            },
            'careTeamMembers' => function ($q) {
                $q->where('type', '=', 'billing_provider');
            },
        ])
                        ->has('patientInfo')
                        ->whereHas('primaryPractice', function ($practice) {
                            $practice->whereHas('ehr', function ($q) {
                                $q->where('name', '=', 'Athena')
                                  ->whereNotNull('external_id');
                            });
                        })
                        ->whereHas('ehrInfo', function ($e) {
                            $e->where('ehr_id', 2);
                        })
                        ->ofType('participant')
                        ->get();


        //updateOrCreate Appointments
        foreach ($patients as $patient) {

            $ehrInfo = $patient->ehrInfo();

            $response = $this->api->getPatientAppointments($ehrInfo->ehr_practice_id, $ehrInfo->ehr_patient_id, false);


            if ($response['totalcount'] == 0) {
                return false;
            }


            $ehrAppointments = $response['appointments'];

            //carbon date
            foreach ($ehrAppointments as $ehrAppointment) {

                //Dummy User to indicate that the appointment is created in Athena
                $athena = User::where('first_name', 'Athena')
                              ->where('last_name', 'API')
                              ->first();

                $provider = $this->api->getBillingProviderName($ehrInfo->ehr_practice_id, $ehrAppointment['providerid']);
                $department = $this->api->getDepartmentInfo($ehrInfo->ehr_practice_id, $ehrAppointment['departmentid']);

                $appointment = Appointment::updateOrCreate([
                    'patient_id'    => $patient->id,
                    'author_id'     => $athena->id,
                    'provider_id'   => null,
                    'was_completed' => 0,
                    'type'          => $ehrAppointment['patientappointmenttypename'],
                    'date'          => $ehrAppointment['date'],
                    'time'          => $ehrAppointment['starttime'],
                    'comment'       => "Appointment regarding " . $ehrAppointment['patientappointmenttypename'] . " to see " . $provider['displayname'] .  " has been scheduled for " . $ehrAppointment['date'] . " at " . $ehrAppointment['starttime'] . "at " . $department['patientdepartmentname'] . ", " . $department['address'] . ", " . $department['city'] . ".",
                ]);




            }
        }


    }
}
