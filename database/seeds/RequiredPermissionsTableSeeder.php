<?php

use App\Permission;
use Illuminate\Database\Seeder;

class RequiredPermissionsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        Permission::truncate();

        foreach ($this->perms() as $perm) {
            Permission::updateOrCreate([
                'name' => $perm['name'],
            ], $perm);
        }
    }

    public function perms()
    {
        $perms = [];

        foreach($this->entityModels() as $entityModel){
            $crud = $this->crudPermission($entityModel);
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
                'display_name' => 'Approve Careplans',
                'description'  => 'Can approve CarePlans with status qa_approved. Changes the CarePlan status to provider_approved.',
            ],
            [
                'name'         => 'care-plan-qa-approve',
                'display_name' => 'CLH Approve Careplan',
                'description'  => 'Can approve CarePlans with status draft. Changes the CarePlan status to qa_approved.',
            ],
            [
                'name'         => 'users-edit-self',
                'display_name' => 'Users - Edit self',
            ],
            [
                'name'         => 'note.send',
                'display_name' => 'Note - Send',
            ]

        ];

        return array_merge($perms, $old);



    }

    private function crudPermission($entityModel) : array
    {
        return [
            [
                'name'         => "$entityModel.create",
                'display_name' => ucfirst($entityModel) . " - " . "Create",
                'description'  => "Create a $entityModel.",
            ],
            [
                'name'         => "$entityModel.read",
                'display_name' => ucfirst($entityModel) . " - " . "Read",
                'description'  => "Read a $entityModel.",
            ],
            [
                'name'         => "$entityModel.update",
                'display_name' => ucfirst($entityModel) . " - " . "Update",
                'description'  => "Update a $entityModel.",
            ],
            [
                'name'         => "$entityModel.delete",
                'display_name' => ucfirst($entityModel) . " - " . "Delete",
                'description'  => "Delete a $entityModel.",
            ],
        ];
    }

    private function entityModels(){
        return collect([
            "note",
            "call",
            "activity",
            "biometric",
            "allergy",
            "symptom",
            "lifestyle",
            "misc",
            "appointment",
            "provider",
            "ccda",
            "medication",
            "patientProblem",
            "patient",
            "careplan",
            "instruction",
            "observation",
            "user",
            "location",
            "practice",
            "nurse",
            "role",
            "practiceStaff",
            "chargeableService",
            "invite",
            "enrollee",
            "careplanAssessment",
            "patientSummary",
            "carePerson",
            "careplan-pdf",
            "workHours",
            "emailSettings",
            "addendum",
            "batch",
            "saas",
            "ambassador",
            "salesReport",
            "ethnicityReport",
            "opsReport",
            "practiceInvoice",
            "excelReport",
            "appConfig",
            "family",
            "permission",
            "nurseInvoice",
            "comment",
            "nurseContactWindow",
            "nurseHoliday",
            "practiceSetting",
            "medicationGroup",
            "nurseReport",
        ]);
    }
}