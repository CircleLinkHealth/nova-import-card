<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Console\Commands;

use CircleLinkHealth\Customer\Entities\Ehr;
use CircleLinkHealth\Customer\Entities\Location;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\Role;
use CircleLinkHealth\Eligibility\Contracts\AthenaApiImplementation;
use Illuminate\Console\Command;

class CreateLocationsFromAthenaApi extends Command
{
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch locations from Athena API, and updateOrCreate them in CPM.';
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'athena:fetch-locations {cpmPracticeId : The practice ID from CPM} {--fetch : Fetch locations from Athena and update or create them in CPM} {--activate : Restore locations we have pulled patients from.}';
    /**
     * @var AthenaApiImplementation
     */
    private $api;
    private $practice;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(?AthenaApiImplementation $api)
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
        if ($this->option('fetch')) {
            $this->warn('Fetching locations from Athena');
            $this->importAthenaLocations();
        }

        if ($this->option('activate')) {
            $this->warn('Activating locations from where we have patients');
            $this->activateLocationsWeHavePatientsFrom();
        }

        $this->comment('Done!');
    }

    public function practice()
    {
        if (is_null($this->practice)) {
            $this->practice = Practice::with('locations')->whereHas('ehr', function ($q) {
                $q->where('id', Ehr::athena()->firstOrFail()->id);
            })->whereNotNull('external_id')->findOrFail($this->argument('cpmPracticeId'));
        }

        return $this->practice;
    }

    private function activateLocationsWeHavePatientsFrom()
    {
        Location::onlyTrashed()->where('practice_id', $this->practice()->id)->whereIn('external_department_id', function ($q) {
            $q->select('ehr_department_id')
                ->from('target_patients')
                ->where('practice_id', $this->practice()->id)
                ->whereNotNull('user_id')
                ->whereIn('user_id', function ($q) {
                    $q->select('user_id')
                        ->from('practice_role_user')
                        ->where('program_id', $this->practice()->id)
                        ->whereIn('role_id', Role::whereIn('name', ['participant', 'survey-only'])->pluck('id')->all())
                        ->distinct()
                    ;
                })
                ->distinct()
            ;
        })->get()->each(function (Location $location) {
            $this->warn("Restoring location[$location->id]");
            $location->restore();
        });
    }

    private function importAthenaLocations()
    {
        collect($this->api->getDepartments($this->practice()->external_id)['departments'] ?? [])->each(function ($aLoc) {
            $cpmLocs = $this->practice()->locations;
            $softDeleteTillAdminApproves = true;

            if ($cpmLocs->contains('external_department_id', $aLoc['departmentid'])) {
                $softDeleteTillAdminApproves = false;
            }

            $this->warn("Processing athenaLocationId[{$aLoc['departmentid']}]");

            $cpmLocation = Location::withTrashed()->updateOrCreate([
                'practice_id'            => $this->practice()->id,
                'external_department_id' => $aLoc['departmentid'],
            ], [
                'name'           => capitalizeWords($aLoc['name']),
                'phone'          => empty($phone = formatPhoneNumberE164($aLoc['phone'] ?? null)) ? null : $phone,
                'fax'            => empty($fax = formatPhoneNumberE164($aLoc['clinicalproviderfax'] ?? $aLoc['fax'] ?? null)) ? null : $fax,
                'address_line_1' => $aLoc['address'],
                'address_line_2' => $aLoc['address2'] ?? null,
                'city'           => capitalizeWords($aLoc['city']),
                'state'          => $aLoc['state'],
                'timezone'       => $aLoc['timezonename'],
                'postal_code'    => $aLoc['zip'],
            ]);

            if (true === $softDeleteTillAdminApproves) {
                $cpmLocation->delete();
            }
        });
    }
}
