<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Database\Seeders;

use CircleLinkHealth\Customer\Entities\Permission;
use Illuminate\Database\Seeder;

class RequiredPermissionsTableSeeder extends Seeder
{
    /**
     * Permissions used for specific business logic related actions.
     *
     * @return array
     */
    public function domainPermissions()
    {
        $perms = [];

        foreach ($this->resources() as $entityModel) {
            $crud  = $this->crudPermission($entityModel);
            $perms = array_merge($perms, $crud);
        }

        $old = [
            [
                'name'         => 'users-view-all',
                'display_name' => 'Users - View All',
            ],
            [
                'name'         => 'users-view-self',
                'display_name' => 'Users - view self',
            ],
            [
                'name'         => 'admin-access',
                'display_name' => 'Admin Access',
                'description'  => 'Grand access to CLH Admin dashboard. This is a legacy permission, so it may not work. Avoid using it unless you know what you are doing.',
            ],
            [
                'name'         => 'post-ccd-to-api',
                'display_name' => 'POST CCDs to API',
                'description'  => 'Can POST CCDs to our API. This is a legacy permission, so it may not work. Avoid using it unless you know what you are doing.',
            ],
            [
                'name'         => 'ccd-import',
                'display_name' => 'Import CCDs',
                'description'  => 'Can use the CCD Importer.',
            ],
            [
                'name'         => 'use-onboarding',
                'display_name' => 'Use Onboarding without a code',
                'description'  => 'Can use Onboarding to set up a Practice.',
            ],
            [
                'name'         => 'care-plan-approve',
                'display_name' => 'Approve all Careplans for a given Practice.',
                'description'  => 'Can approve CarePlans with status rn_approved. Changes the CarePlan status to provider_approved.',
            ],
            [
                'name'         => 'care-plan-qa-approve',
                'display_name' => 'CLH Approve Careplan',
                'description'  => 'Can approve CarePlans with status draft. Changes the CarePlan status to qa_approved.',
            ],
            [
                'name'         => 'care-plan-rn-approve',
                'display_name' => 'RN Approve Careplan',
                'description'  => 'Can approve CarePlans with status qa_approved. Changes the CarePlan status to rn_approved.',
            ],
            [
                'name'         => 'users-edit-self',
                'display_name' => 'Users - Edit self',
            ],
            [
                'name'         => 'note.send',
                'display_name' => 'Note - Send',
            ],
            [
                'name'         => 'legacy-bhi-consent-decision.create',
                'display_name' => 'Allows to store a consent or rejection for BHI.',
            ],
            [
                'name'         => 'practice-admin',
                'display_name' => 'Admin access for privileged users of practice',
            ],
            [
                'name'         => 'change-patient-enrollment-status',
                'display_name' => 'Allows user to change patient enrollment status e.g. to enrolled, withdrawn etc.',
            ],
            [
                'name'         => 'has-schedule',
                'display_name' => 'Allows user to view schedule, scheduled activities and work-schedule. ',
            ],
            [
                'name'         => 'vitals-survey-complete',
                'display_name' => 'Allows user to complete the AWV Vitals Survey',
            ],
            [
                'name'         => 'ehr-report-writer-access',
                'display_name' => 'Has access to EHR Report Writer panel.',
            ],
            [
                'name'         => 'phi.read',
                'display_name' => 'Can view PHI.',
            ],
        ];

        return array_merge($perms, $old);
    }

    /**
     * Populate the Permissions table.
     */
    public function run()
    {
        foreach ($this->domainPermissions() as $perm) {
            Permission::updateOrCreate([
                'name' => $perm['name'],
            ], $perm);
        }
    }

    /**
     * Create CRUD permissions for a Resource.
     *
     * @param $resource
     */
    private function crudPermission($resource): array
    {
        return [
            [
                'name'         => "$resource.create",
                'display_name' => ucfirst($resource).' - '.'Create',
                'description'  => "Create a $resource.",
            ],
            [
                'name'         => "$resource.read",
                'display_name' => ucfirst($resource).' - '.'Read',
                'description'  => "Read a $resource.",
            ],
            [
                'name'         => "$resource.update",
                'display_name' => ucfirst($resource).' - '.'Update',
                'description'  => "Update a $resource.",
            ],
            [
                'name'         => "$resource.delete",
                'display_name' => ucfirst($resource).' - '.'Delete',
                'description'  => "Delete a $resource.",
            ],
        ];
    }

    /**
     * The Resources (Models) we are creating CRUD permissions for.
     *
     * @return \Illuminate\Support\Collection
     */
    private function resources()
    {
        return collect([
            'note',
            'call',
            'activity',
            'biometric',
            'allergy',
            'symptom',
            'lifestyle',
            'misc',
            'appointment',
            'provider',
            'ccda',
            'medication',
            'patientProblem',
            'patient',
            'careplan',
            'instruction',
            'observation',
            'user',
            'location',
            'practice',
            'nurse',
            'role',
            'practiceStaff',
            'chargeableService',
            'invite',
            'enrollee',
            'careplanAssessment',
            'patientSummary',
            'carePerson',
            'careplan-pdf',
            'workHours',
            'emailSettings',
            'addendum',
            'batch',
            'saas',
            'ambassador',
            'salesReport',
            'ethnicityReport',
            'opsReport',
            'practiceInvoice',
            'excelReport',
            'appConfig',
            'family',
            'permission',
            'nurseInvoice',
            'comment',
            'nurseContactWindow',
            'nurseHoliday',
            'practiceSetting',
            'medicationGroup',
            'nurseReport',
            'offlineActivityRequest',
            'offlineActivity',
            'attestedProblems',
            'phoneNumber',
        ]);
    }
}
