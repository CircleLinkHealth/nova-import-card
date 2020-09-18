<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Eligibility\Console\Athena;

use CircleLinkHealth\Eligibility\Services\AthenaAPI\Calls;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\SharedModels\Entities\CarePlan;
use Illuminate\Console\Command;

class PostPatientCarePlanAsAppointmentNote extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Post patient care plan link to EHR';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'athena:postPatientNote';

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
        $response = CarePlan::with([
            'patient.primaryPractice' => function ($practice) {
                $practice->whereHas('ehr', function ($q) {
                    $q->where('name', '=', 'Athena')
                        ->whereNotNull('external_id');
                });
            },
            'patient.patientInfo',
        ])
            ->whereHas('patient', function ($p) {
                $p->where('ccm_status', Patient::TO_ENROLL)
                    ->has('patientInfo');
            })
            ->get()
            ->map(function ($c) {
                $link = route('patient.careplan.print', ['patientId' => $c->user_id]);

                $appointments = $this->api->getPatientAppointments(
                    $athenaPracticeId = $c->patient
                        ->primaryPractice
                        ->external_id,
                    $c->patient->patientInfo->mrn_number,
                    false
                );
                $sortedAppointments = collect($appointments['appointments'])->sortBy('date');
                $nextAppointment = $sortedAppointments->first();

                $response = $this->api->postAppointmentNotes(
                    $athenaPracticeId,
                    $nextAppointment['appointmentid'],
                    $link,
                    true
                );
            });
    }
}
