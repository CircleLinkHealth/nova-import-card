<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Console\Commands;

use App\Location;
use App\Services\ReportsService;
use App\User;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;

class GeneratePatientReports extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Given a comma separated list of user ids, this command creates Patient Reports for Aprima';

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'make:reports';

    /**
     * Create a new command instance.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $patient_ids = explode(',', $this->argument('patientIds'));
        foreach ($patient_ids as $patient_id) {
            //Check if user exists
            $patient = User::find($patient_id);
            if ( ! $patient) {
                $this->error(' User Not Found: '.$patient_id.' ');
                continue;
            }

            //Check Status
            $status = $patient->getCarePlanStatus();
            if ('provider_approved' != $status) {
                $this->error(' User Not Provider Approved: '.$patient_id.' ');
                continue;
            }
            //Check if the provider approver is set
            $provider_id = $patient->getCarePlanProviderApprover();
            if ( ! $provider_id) {
                $this->error(' Approving Provider Not Found: '.$patient_id.' ');
                continue;
            }
            $locationId = $patient->getpreferredContactLocation();

            if ( ! $locationId) {
                $this->error(' Location Not Found For: '.$patient_id.' ');
                continue;
            }

            $locationObj = Location::find($locationId);

            if ( ! $locationObj) {
                $this->error(' Location Object Not Found For: '.$patient_id.' ');
                continue;
            }

            if (Location::UPG_PARENT_LOCATION_ID != $locationObj->parent_id) {
                $this->error(' Location Does Not Belong to Aprima for User: '.$patient_id.' ');
                continue;
            }

            if ( ! empty($locationObj) && Location::UPG_PARENT_LOCATION_ID == $locationObj->parent_id) {
                (new ReportsService())->createAprimaPatientCarePlanPdfReport($patient, $provider_id);
                $this->info('Report Created for User: '.$patient_id.' ');
            }

            $this->info('Report Creation Complete');
        }
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return [
            ['patientIds', InputArgument::REQUIRED, 'List of patients to create reports for...'],
        ];
    }
}
