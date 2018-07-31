<?php

use App\Permission;
use App\Role;
use Illuminate\Database\Seeder;

class RequiredRolesPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(RequiredPermissionsTableSeeder::class);

        $mdally = Role::where('name', 'mdally')->first();
        if ($mdally){
            $mdally->delete();
        }

        foreach ($this->roles() as $role) {
            $permissionsArr = $role['permissions'];

            unset($role['permissions']);

            $role = Role::updateOrCreate(['name' => $role['name']], $role);

            $permissionIds = Permission::whereIn('name', $permissionsArr)
                                       ->pluck('id')->all();

            $role->perms()->sync($permissionIds);

            $name = $role['name'];
            $this->command->info("role $name created");
        }

        $this->giveAdminsAllPermissions('administrator');
        $this->giveAdminsAllPermissions('saas-admin');

        $this->command->info('all roles and permissions created');
    }

    public function roles()
    {
        return [
            [
                'name'         => 'administrator',
                'display_name' => 'Administrator',
                'description'  => 'Administrator',
                'permissions'  => [],
            ],
            [
                'name'         => 'participant',
                'display_name' => 'Participant',
                'description'  => 'Participant',
                'permissions'  => [
                    'users-view-self',
                    'patient.read',
                    'activity.read',
                    'note.read',
                    'appointment.read',
                    'provider.read',
                    'pdf.create',
                    'observation.create',
                    'observation.update',
                    'observation.read',
                ],
            ],
            [
                'name'         => 'api-ccd-vendor',
                'display_name' => 'API CCD Vendor',
                'description'  => 'Is able to post CCDs to our API',
                'permissions'  => [
                    'post-ccd-to-api',
                    'patient.create',
                    'patient.read',
                    'patient.update',
                    'provider.read',
                    'note.read',
                    'activity.read',
                    'location.read',
                    'practice.read',
                    'pdf.create',
                ],
            ],
            [
                'name'         => 'api-data-consumer',
                'display_name' => 'API Data Consumer',
                'description'  => 'Is able to receive PDF Reports and CCM Time from our API',
                'permissions'  => [
                    'patient.create',
                    'patient.read',
                    'patient.update',
                    'provider.read',
                    'note.read',
                    'activity.read',
                    'location.read',
                    'practice.read',
                    'pdf.create',
                ],
            ],
            [
                'name'         => 'viewer',
                'display_name' => 'Viewer',
                'description'  => '',
                'permissions'  => [
                    'users-view-all',
                    'users-view-self',
                    'user.read',
                    'patient.create',
                    'patient.read',
                    'patient.update',
                    'careplan.read',
                    'provider.read',
                    'activity.read',
                    'location.read',
                    'practice.read',
                    'activity.read',
                    'biometric.read',
                    'biometric.update',
                    'medication.read',
                    'medication.update',
                    'note.read',
                    'appointment.read',
                    'patientProblem.read',
                    'misc.read',
                    'observation.read',
                    'patientSummary.read',
                    'pdf.create',
                    'pdf.read',
                    'carePerson.read',
                ],
            ],
            [
                'name'         => 'office_admin',
                'display_name' => 'Office Admin',
                'description'  => 'Not CCM countable.',
                'permissions'  => [
                    'users-view-all',
                    'users-view-self',
                    'user.read',
                    'patient.read',
                    'patient.update',
                    'careplan.read',
                    'careplan.update',
                    'provider.read',
                    'note.create',
                    'note.read',
                    'note.update',
                    'activity.create',
                    'activity.read',
                    'activity.update',
                    'location.read',
                    'practice.read',
                    'biometric.read',
                    'biometric.update',
                    'medication.read',
                    'medication.update',
                    'patientProblem.read',
                    'patientProblem.update',
                    'provider.read',
                    'appointment.read',
                    'appointment.update',
                    'pdf.create',
                    'pdf.read',
                    'carePerson.read',
                    'nurse.read',
                    'comment.create',
                    'comment.read',
                    'comment.update',

                ],
            ],
            [
                'name'         => 'aprima-api-location',
                'display_name' => 'API Data Consumer and CCD Vendor.',
                'description'  => 'This role is JUST FOR APRIMA! Is able to receive PDF Reports and CCM Time from our API. Is able to post CCDs to our API.',
                'permissions'  => [
                    'post-ccd-to-api',
                ],
            ],
            [
                'name'         => 'no-ccm-care-center',
                'display_name' => 'Non CCM Care Center',
                'description'  => 'Care Center',
                'permissions'  => [
                    'ccd-import',
                    'post-ccd-to-api',
                    'users-edit-self',
                    'users-view-all',
                    'users-view-self',
                    'note.create',
                    'note.read',
                    'note.update',
                    'call.create',
                    'call.read',
                    'call.update',
                    'patient.create',
                    'patient.read',
                    'patient.update',
                    'user.create',
                    'user.read',
                    'user.update',
                    'appConfig.create',
                    'appConfig.read',
                    'appConfig.update',
                    'activity.create',
                    'activity.read',
                    'activity.update',
                    'appointment.create',
                    'appointment.read',
                    'appointment.update',
                    'observation.create',
                    'observation.read',
                    'observation.update',
                    'role.read',
                    'role.update',
                    'practice.read',
                    'practice.update',
                    'permission.read',
                    'permission.update',
                    'careplan.read',
                    'careplan.update',
                    'location.create',
                    'location.read',
                    'location.update',
                    'provider.read',
                    'provider.update',
                    'biometric.read',
                    'biometric.update',
                    'medication.read',
                    'medication.update',
                    'patientProblem.read',
                    'patientProblem.update',
                    'misc.read',
                    'misc.update',
                    'patientSummary.read',
                    'patientSummary.update',
                    'pdf.create',
                    'pdf.read',
                    'pdf.update',
                    'carePerson.read',
                    'comment.create',
                    'comment.read',
                    'comment.update',
                ],
            ],
            [
                'name'         => 'no-access',
                'display_name' => 'No Access',
                'description'  => '',
                'permissions'  => [],
            ],
            [
                'name'         => 'administrator-view-only',
                'display_name' => 'Administrator - View Only',
                'description'  => 'A special administrative account where you can view the admin but not perform actions',
                'permissions'  => [
                    'admin-access',
                    'users-edit-self',
                    'users-view-all',
                    'users-view-self',
                    'patient.read',
                    'patient.update',
                    'biometric.update',
                    'medication.update',
                    'pdf.create',
                    'note.read',
                    'call.read',
                    'activity.read',
                    'biometric.read',
                    'allergy.read',
                    'symptom.read',
                    'lifestyle.read',
                    'misc.read',
                    'appointment.read',
                    'provider.read',
                    'ccda.read',
                    'medication.read',
                    'patientProblem.read',
                    'instruction.read',
                    'observation.read',
                    'user.read',
                    'location.read',
                    'practice.read',
                    'nurse.read',
                    'role.read',
                    'practiceStaff.read',
                    'chargeableService.read',
                    'invite.read',
                    'enrollee.read',
                    'careplanAssessment.read',
                    'patientSummary.read',
                    'carePerson.read',
                    'pdf.read',
                    'workHours.read',
                    'emailSettings.read',
                    'addendum.read',
                    'batch.read',
                    'saas.read',
                    'ambassador.read',
                    'salesReport.read',
                    'ethnicityReport.read',
                    'opsReport.read',
                    'practiceInvoice.read',
                    'practiceInvoice.create',
                    'excelReport.read',
                    'excelReport.create',
                    'appConfig.read',
                    'family.read',
                    'permission.read',
                    'nurseInvoice.create',
                    'nurseInvoice.read',
                    'comment.read',
                    'nurseContactWindow.read',
                    'nurseHoliday.read',
                    'practiceSetting.read',
                    'medicationGroup.read'
                ],
            ],
            [
                'name'         => 'no-access',
                'display_name' => 'No Access',
                'description'  => '',
                'permissions'  => [],
            ],
            [
                'name'         => 'practice-lead',
                'display_name' => 'Program Lead',
                'description'  => 'The provider that created the practice.',
                'permissions'  => [
                    'users-view-all',
                    'users-view-self',
                    'call.create',
                    'call.read',
                    'call.update',
                    'call.delete',
                    'patient.create',
                    'patient.read',
                    'patient.update',
                    'provider.create',
                    'provider.read',
                    'provider.update',
                    'nurse.read',
                    'note.create',
                    'note.read',
                    'note.update',
                    'activity.create',
                    'activity.read',
                    'activity.update',
                    'activity.delete',
                    'appointment.create',
                    'appointment.read',
                    'appointment.update',
                    'allergy.read',
                    'misc.read',
                    'observation.create',
                    'observation.read',
                    'observation.update',
                    'observation.delete',
                    'biometric.create',
                    'biometric.read',
                    'biometric.update',
                    'biometric.delete',
                    'medication.create',
                    'medication.read',
                    'medication.update',
                    'medication.delete',
                    'medicationGroup.create',
                    'medicationGroup.read',
                    'medicationGroup.update',
                    'medicationGroup.delete',
                    'patientProblem.create',
                    'patientProblem.read',
                    'patientProblem.update',
                    'patientSummary.read',
                    'patientSummary.update',
                    'chargeableService.read',
                    'chargeableService.update',
                    'careplan.create',
                    'careplan.read',
                    'careplan.update',
                    'location.create',
                    'location.read',
                    'location.update',
                    'practice.read',
                    'practice.update',
                    'pdf.create',
                    'pdf.read',
                    'pdf.update',
                    'carePerson.read',
                    'carePerson.create',
                    'carePerson.update',
                    'carePerson.delete',
                    'symptom.create',
                    'symptom.read',
                    'symptom.update',
                    'symptom.delete',
                    'instruction.create',
                    'instruction.read',
                    'instruction.update',
                    'instruction.delete',
                    'lifestyle.create',
                    'lifestyle.read',
                    'lifestyle.update',
                    'lifestyle.delete',
                    'comment.create',
                    'comment.read',
                    'comment.update',
                    'comment.delete',
                ],
            ],
            [
                'name'         => 'registered-nurse',
                'display_name' => 'Registered Nurse',
                'description'  => 'A nurse that belongs to a practice and not our care center.',
                'permissions'  => [
                    'care-plan-approve',
                    'users-view-all',
                    'users-view-self',
                    'patient.create',
                    'patient.read',
                    'patient.update',
                    'observation.create',
                    'observation.read',
                    'observation.update',
                    'observation.delete',
                    'careplan.read',
                    'careplan.update',
                    'location.read',
                    'practice.read',
                    'activity.create',
                    'activity.read',
                    'activity.update',
                    'provider.read',
                    'biometric.read',
                    'biometric.update',
                    'medication.read',
                    'medication.update',
                    'patientProblem.read',
                    'misc.read',
                    'patientSummary.read',
                    'note.create',
                    'note.read',
                    'note.update',
                    'appointment.create',
                    'appointment.read',
                    'appointment.update',
                    'pdf.create',
                    'pdf.read',
                    'pdf.update',
                    'carePerson.read',
                    'carePerson.create',
                    'carePerson.update',
                    'lifestyle.create',
                    'lifestyle.read',
                    'lifestyle.update',
                    'lifestyle.delete',
                    'comment.create',
                    'comment.read',
                    'comment.update'
                ],
            ],
            [
                'name'         => 'specialist',
                'display_name' => 'Specialist',
                'description'  => 'An outside specialist doctor.',
                'permissions'  => [
                    'users-view-all',
                    'users-view-self',
                    'patient.create',
                    'patient.read',
                    'patient.update',
                    'provider.read',
                    'observation.create',
                    'observation.read',
                    'careplan.read',
                    'careplan.update',
                    'note.create',
                    'note.read',
                    'note.update',
                    'activity.create',
                    'activity.read',
                    'activity.update',
                    'location.read',
                    'practice.read',
                    'biometric.read',
                    'biometric.update',
                    'medication.read',
                    'medication.update',
                    'patientProblem.read',
                    'misc.read',
                    'patientSummary.read',
                    'appointment.read',
                    'pdf.create'

                ],
            ],
            [
                'name'         => 'salesperson',
                'display_name' => 'Salesperson',
                'description'  => 'A Salesperson',
                'permissions'  => [
                    'use-onboarding',
                    'patient.create',
                    'patient.read',
                    'patient.update',
                    'provider.read',
                    'note.read',
                    'activity.read',
                    'location.read',
                    'practice.read',
                    'pdf.create',
                ],
            ],
            [
                'name'         => 'care-ambassador',
                'display_name' => 'Care Ambassador',
                'description'  => 'Makes calls to enroll patients.',
                'permissions'  => [
                    'careplan.read',
                    'careplan.update',
                    'careplanAssessment.read',
                    'enrollee.read',
                    'enrollee.update',
                    'call.read',
                    'practice.read',
                    'provider.read',
                    'ambassador.read',
                ],
            ],
            [
                'name'         => 'care-ambassador-view-only',
                'display_name' => 'Care Ambassador - View Only',
                'description'  => 'Makes calls to enroll patients, and can see patient data for specific Practices',
                'permissions'  => [
                    'careplan.read',
                    'careplan.update',
                    'careplanAssessment.read',
                    'enrollee.read',
                    'enrollee.update',
                    'call.read',
                    'practice.read',
                    'provider.read',
                    'ambassador.read',
                    'patient.read',
                    'observation.read',
                    'note.read',
                    'activity.read',
                    'location.read',
                    'biometric.read',
                    'biomteric.update',
                    'medication.read',
                    'medication.update',
                    'patientProblem.read',
                    'misc.read',
                    'patientSummary.read',
                    'appointment.read',
                    'pdf.create',
                    'users-view-all',
                ],
            ],
            [
                'name'         => 'med_assistant',
                'display_name' => 'Medical Assistant',
                'description'  => 'CCM Countable.',
                'permissions'  => [
                    'care-plan-approve',
                    'users-view-all',
                    'users-view-self',
                    'patient.create',
                    'patient.read',
                    'patient.update',
                    'careplan.read',
                    'careplan.update',
                    'provider.read',
                    'note.create',
                    'note.read',
                    'note.update',
                    'activity.create',
                    'activity.read',
                    'activity.update',
                    'location.read',
                    'practice.read',
                    'biometric.read',
                    'biometric.update',
                    'medication.read',
                    'medication.update',
                    'patientProblem.read',
                    'misc.read',
                    'observation.read',
                    'patientSummary.read',
                    'appointment.read',
                    'pdf.create',
                ],
            ],
            [
                'name'         => 'provider',
                'display_name' => 'Provider',
                'description'  => 'Provider',
                'permissions'  => [
                    'care-plan-approve',
                    'users-view-all',
                    'users-view-self',
                    'call.create',
                    'call.read',
                    'call.update',
                    'call.delete',
                    'patient.create',
                    'patient.read',
                    'patient.update',
                    'provider.create',
                    'provider.read',
                    'provider.update',
                    'provider.delete',
                    'nurse.create',
                    'nurse.read',
                    'nurse.update',
                    'nurse.delete',
                    'note.create',
                    'note.read',
                    'note.update',
                    'activity.create',
                    'activity.read',
                    'activity.update',
                    'activity.delete',
                    'appointment.create',
                    'appointment.read',
                    'appointment.update',
                    'allergy.create',
                    'allergy.read',
                    'allergy.update',
                    'allergy.delete',
                    'misc.create',
                    'misc.read',
                    'misc.update',
                    'misc.delete',
                    'observation.create',
                    'observation.read',
                    'observation.update',
                    'observation.delete',
                    'biometric.create',
                    'biometric.read',
                    'biometric.update',
                    'biometric.delete',
                    'medication.create',
                    'medication.read',
                    'medication.update',
                    'medication.delete',
                    'patientProblem.create',
                    'patientProblem.read',
                    'patientProblem.update',
                    'patientSummary.read',
                    'patientSummary.update',
                    'chargeableService.read',
                    'chargeableService.update',
                    'careplan.create',
                    'careplan.read',
                    'careplan.update',
                    'location.create',
                    'location.read',
                    'location.update',
                    'careplanAssessment.read',
                    'careplanAssessment.create',
                    'careplanAssessment.update',
                    'careplanAssessment.delete',
                    'practice.read',
                    'practice.update',
                    'pdf.create',
                    'pdf.read',
                    'pdf.update',
                    'pdf.delete',
                    'carePerson.read',
                    'carePerson.create',
                    'carePerson.update',
                    'carePerson.delete',
                    'symptom.create',
                    'symptom.read',
                    'symptom.update',
                    'symptom.delete',
                    'instruction.create',
                    'instruction.read',
                    'instruction.update',
                    'instruction.delete',
                    'medicationGroup.create',
                    'medicationGroup.read',
                    'medicationGroup.update',
                    'medicationGroup.delete',
                    'lifestyle.create',
                    'lifestyle.read',
                    'lifestyle.update',
                    'lifestyle.delete',
                    'comment.create',
                    'comment.read',
                    'comment.update',
                    'comment.delete',

                ],
            ],
            [
                'name'         => 'care-center',
                'display_name' => 'Care Coach',
                'description'  => 'CLH Nurses, the ones who make calls to patients. CCM countable.',
                'permissions'  => [
                    'care-plan-qa-approve',
                    'users-view-all',
                    'users-view-self',
                    'patient.create',
                    'patient.read',
                    'patient.update',
                    'user.create',
                    'user.read',
                    'user.update',
                    'careplan.read',
                    'careplan.update',
                    'biometric.create',
                    'biometric.read',
                    'biometric.update',
                    'patientProblem.read',
                    'patientProblem.update',
                    'patientProblem.create',
                    'patientProblem.delete',
                    'patientSummary.read',
                    'patientSummary.update',
                    'misc.read',
                    'misc.create',
                    'misc.update',
                    'misc.delete',
                    'medication.create',
                    'medication.read',
                    'medication.update',
                    'medication.delete',
                    'activity.create',
                    'activity.read',
                    'activity.update',
                    'appointment.create',
                    'appointment.read',
                    'provider.read',
                    'practice.read',
                    'note.create',
                    'note.read',
                    'note.update',
                    'call.create',
                    'call.read',
                    'call.update',
                    'call.delete',
                    'location.read',
                    'observation.create',
                    'observation.read',
                    'observation.update',
                    'observation.delete',
                    'role.read',
                    'permission.read',
                    'nurseContactWindow.create',
                    'nurseContactWindow.read',
                    'nurseContactWindow.update',
                    'nurseContactWindow.delete',
                    'nurseHoliday.create',
                    'nurseHoliday.read',
                    'nurseHoliday.update',
                    'nurseHoliday.delete',
                    'pdf.create',
                    'pdf.read',
                    'pdf.update',
                    'carePerson.read',
                    'carePerson.create',
                    'carePerson.update',
                    'carePerson.delete',
                    'symptom.create',
                    'symptom.read',
                    'symptom.update',
                    'symptom.delete',
                    'instruction.create',
                    'instruction.read',
                    'instruction.update',
                    'instruction.delete',
                    'medicationGroup.create',
                    'medicationGroup.read',
                    'medicationGroup.update',
                    'medicationGroup.delete',
                    'lifestyle.create',
                    'lifestyle.read',
                    'lifestyle.update',
                    'lifestyle.delete',
                    'comment.create',
                    'comment.read',
                    'comment.update',
                ],
            ],
            [
                'name'         => 'saas-admin',
                'display_name' => 'SAAS Admin',
                'description'  => 'An admin for CPM Software-As-A-Service.',
                'permissions'  => [

                ],
            ],
            [
                'name'         => 'saas-admin-view-only',
                'display_name' => 'Saas Admin - View Only',
                'description'  => 'Created for MDAlly, a partner which white-labels our CCM service to their customers',
                'permissions'  => [
                    'admin-access',
                    'users-edit-self',
                    'users-view-all',
                    'users-view-self',
                    'patient.create',
                    'patient.read',
                    'patient.update',
                    'patient.delete',
                    'biometric.update',
                    'medication.update',
                    'pdf.create',
                    'note.read',
                    'call.read',
                    'activity.read',
                    'biometric.read',
                    'allergy.read',
                    'symptom.read',
                    'lifestyle.read',
                    'misc.read',
                    'appointment.read',
                    'provider.create',
                    'provider.read',
                    'provider.update',
                    'provider.delete',
                    'ccda.read',
                    'medication.read',
                    'patientProblem.read',
                    'instruction.read',
                    'observation.read',
                    'user.create',
                    'user.read',
                    'user.update',
                    'user.delete',
                    'location.read',
                    'practice.create',
                    'practice.read',
                    'practice.update',
                    'practice.delete',
                    'nurse.create',
                    'nurse.read',
                    'nurse.update',
                    'nurse.delete',
                    'role.read',
                    'practiceStaff.create',
                    'practiceStaff.read',
                    'practiceStaff.update',
                    'practiceStaff.delete',
                    'chargeableService.read',
                    'invite.read',
                    'enrollee.read',
                    'careplanAssessment.read',
                    'patientSummary.read',
                    'carePerson.create',
                    'carePerson.read',
                    'carePerson.update',
                    'carePerson.delete',
                    'pdf.read',
                    'workHours.read',
                    'emailSettings.create',
                    'emailSettings.read',
                    'emailSettings.update',
                    'emailSettings.delete',
                    'addendum.read',
                    'batch.read',
                    'saas.create',
                    'saas.read',
                    'saas.update',
                    'saas.delete',
                    'ambassador.create',
                    'ambassador.read',
                    'ambassador.update',
                    'ambassador.delete',
                    'salesReport.read',
                    'ethnicityReport.read',
                    'opsReport.read',
                    'practiceInvoice.read',
                    'practiceInvoice.create',
                    'practiceInvoice.update',
                    'practiceInvoice.delete',
                    'excelReport.read',
                    'excelReport.create',
                    'appConfig.read',
                    'family.read',
                    'permission.read',
                    'nurseInvoice.create',
                    'nurseInvoice.read',
                    'comment.read',
                    'nurseContactWindow.read',
                    'nurseHoliday.read',
                    'practiceSetting.create',
                    'practiceSetting.read',
                    'practiceSetting.update',
                    'practiceSetting.delete',
                    'medicationGroup.read',
                    'ccd-import',
                    'use-onboarding',
                ],
            ],
        ];
    }

    public function giveAdminsAllPermissions($roleName)
    {
        $adminRole = Role::whereName($roleName)->first();

        $permissions = Permission::where('name', '!=', 'care-plan-approve')
                                 ->get();

        $adminRole->perms()->sync($permissions->pluck('id')->all());
    }
}
