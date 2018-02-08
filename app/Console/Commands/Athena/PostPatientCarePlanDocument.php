<?php

namespace App\Console\Commands;

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
    protected $signature = 'command:postPatientDocument';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Post patient care plan link to EHR';

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
        //query for CarePlans 'to_enroll', map() each careplan to get link to post to EHR

        $response = CarePlan::with([
            'patient' => function ($query) {
                $query->with([
                    'primaryPractice' => function ($practice) {
                        $practice->whereHas('ehr', function ($q) {
                            $q->where('name', '=', 'Athena')
                              ->whereNotNull('external_id');
                        });

                    },
                ])->get()
                      ->map(function ($c) {
                          $link = route('patient.careplan.print', ['patientId' => $c->user_id]);

                          $response = $this->api->postPatientDocument($c->user_id, $practiceId, $link,
                              $departmentId);
                      });;
            },
        ]);

    }
}
