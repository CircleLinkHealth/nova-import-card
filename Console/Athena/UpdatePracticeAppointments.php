<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Console\Athena;

use CircleLinkHealth\SharedModels\Entities\Call;
use App\Services\AthenaAPI\Calls;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Appointment;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Console\Command;

class UpdatePracticeAppointments extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Retrieves all appointments of a Patient from Athena and syncs with the database';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'athena:updateAppointments';

    private $api;

    /**
     * Create a new command instance.
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
            'ehrInfo' => function ($e) {
                $e->whereHas('ehr', function ($q) {
                    $q->where('name', '=', 'Athena');
                });
            },
            'primaryPractice' => function ($practice) {
                $practice->whereHas('ehr', function ($q) {
                    $q->where('name', '=', 'Athena');
                });
            },
            'careTeamMembers' => function ($q) {
                $q->where('type', '=', 'billing_provider');
            },
        ])
            ->has('patientInfo')
            ->whereHas('primaryPractice', function ($practice) {
                $practice->whereHas('ehr', function ($q) {
                    $q->where('name', '=', 'Athena');
                });
            })
            ->whereHas('ehrInfo', function ($e) {
                $e->whereHas('ehr', function ($q) {
                    $q->where('name', '=', 'Athena');
                });
            })
            ->ofType('participant')
            ->get();

        //updateOrCreate Appointments
        foreach ($patients as $patient) {
            $ehrInfo = $patient->ehrInfo;

            $response = $this->api->getPatientAppointments($ehrInfo->ehr_practice_id, $ehrInfo->ehr_patient_id, false);

            if (0 == $response['totalcount']) {
                return false;
            }

            $ehrAppointments = $response['appointments'];

            foreach ($ehrAppointments as $ehrAppointment) {
                //Dummy User to indicate that the appointment is created in Athena
                $athena = User::where('last_name', 'API')
                    ->where('first_name', 'Athena')
                    ->first();

                $providerResponse = $this->api->getBillingProviderName(
                    $ehrInfo->ehr_practice_id,
                    $ehrAppointment['providerid']
                );
                $provider           = $providerResponse[0];
                $departmentResponse = $this->api->getDepartmentInfo(
                    $ehrInfo->ehr_practice_id,
                    $ehrAppointment['departmentid']
                );
                $department = $departmentResponse[0];

                $date = new Carbon($ehrAppointment['date']);

                $appointment = Appointment::updateOrCreate([
                    'patient_id'  => $patient->id,
                    'author_id'   => $athena->id,
                    'provider_id' => null,
                    'date'        => $date->toDateString(),
                    'time'        => $ehrAppointment['starttime'],
                ], [
                    'was_completed' => 0,
                    'type'          => $ehrAppointment['patientappointmenttypename'],
                    'comment'       => 'Appointment regarding '.$ehrAppointment['patientappointmenttypename'].' to see '.$provider['displayname'].' has been scheduled for '.$ehrAppointment['date'].' at '.$ehrAppointment['starttime'].' at '.$department['patientdepartmentname'].', '.$department['address'].', '.$department['city'].'.',
                ]);

                $call = Call::where('inbound_cpm_id', $patient->id)->orderBy('id', 'desc')->first();

                if ($call) {
                    $call->attempt_note = $appointment->comment;
                    $call->save();
                }
            }
        }
    }
}
