<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\PracticePatientsView;
use Illuminate\Console\Command;

class GetPracticePatients extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get number of patients for practice';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'practice:patients';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $practice  = Practice::find(8);
        $start     = microtime(true);
        $patients1 = $practice->patients()->with(['carePlan', 'patientInfo'])->orderBy('id')->get([
            'id',
            'first_name',
            'last_name',
            'suffix',
            'city',
            'state',
        ])->map(function ($patient) {
            return [
                'id'         => $patient->id,
                'first_name' => $patient->getFirstName(),
                'last_name'  => $patient->getLastName(),
                'suffix'     => $patient->getSuffix(),
                'full_name'  => $patient->getFullName(),
                'city'       => $patient->city,
                'state'      => $patient->state,
                'status'     => optional($patient->carePlan)->status,
                'ccm_status' => optional($patient->patientInfo)->ccm_status,
            ];
        })->toArray();
        $time1 = microtime(true) - $start;

        $start     = microtime(true);
        $query2    = PracticePatientsView::where('program_id', '=', 8)->orderBy('id');
        $patients2 = $query2
            ->get()
            ->map(function ($patient) {
                $firstName = ucfirst(strtolower($patient->first_name));
                $lastName = ucfirst(strtolower($patient->last_name));
                $suffix = $patient->suffix ?? '';
                $fullName = trim(ucwords("${firstName} ${lastName} ${suffix}"));

                return [
                    'id'         => $patient->id,
                    'first_name' => $firstName,
                    'last_name'  => $lastName,
                    'suffix'     => $suffix,
                    'full_name'  => $fullName,
                    'city'       => $patient->city,
                    'state'      => $patient->state,
                    'status'     => $patient->status,
                    'ccm_status' => $patient->ccm_status,
                ];
            })
            ->toArray();
        $time2 = microtime(true) - $start;

        // validate we get correct results
        $len = sizeof($patients1);
        for ($i = 0; $i < $len; ++$i) {
            $p1 = $patients1[$i];
            $p2 = $patients2[$i];
            if ($p1['id'] !== $p2['id'] ||
                $p1['first_name'] !== $p2['first_name'] ||
                $p1['last_name'] !== $p2['last_name'] ||
                $p1['suffix'] !== $p2['suffix'] ||
                $p1['full_name'] !== $p2['full_name'] ||
                $p1['city'] !== $p2['city'] ||
                $p1['state'] !== $p2['state'] ||
                $p1['status'] !== $p2['status'] ||
                $p1['ccm_status'] !== $p2['ccm_status']) {
                throw new \Exception("$i do not match");
            }
        }

        $this->info("$time1 | $time2");
    }
}
