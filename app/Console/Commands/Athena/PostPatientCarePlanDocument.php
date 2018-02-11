<?php

namespace App\Console\Commands\Athena;

use App\CarePlan;
use App\Services\AthenaAPI\Calls;
use Illuminate\Console\Command;

class PostPatientCarePlanDocument extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'athena:postPatientDocument';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Post patient care plan link to EHR';

    private $api;

    /**
     * Create a new command instance.
     *
     * @param Calls $api
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
        $response = CarePlan::with([
            'patient.primaryPractice' => function ($practice) {
                $practice->whereHas('ehr', function ($q) {
                    $q->where('name', '=', 'Athena')
                      ->whereNotNull('external_id');
                });
            },
        ])
            ->where('status', '=', CarePlan::TO_ENROLL)
                            ->get()
                            ->map(function ($c) {
                                $link = route('patient.careplan.print', ['patientId' => $c->user_id]);

                                $practiceId = $c->patient
                                    ->primaryPractice
                                    ->external_id;

                                $appointments = $this->api->getPatientAppointments($practiceId, $c->user_id, false);

                                foreach ($appointments as $appointment) {

                                    $departmentId   = $appointment['departmentid'];
                                    $appointmentId = $appointment['appointmentid'];

                                    //need to pass in appointment id
                                    $response = $this->api->postPatientDocument($c->user_id, $practiceId, $link,
                                        $departmentId, $appointmentId);
                                }

                            });
    }
}
