<?php

use App\CarePlan;
use App\PatientInfo;
use Illuminate\Database\Migrations\Migration;

class AttachCarePlans extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //this is to map the fields we are migrating from PatientInfo to CarePlan
        //CarePlan field => PatientInfo Field
        $patientInfoToCarePlanMap = [
            'provider_approver_id' => 'careplan_provider_approver',
            'qa_approver_id'       => 'careplan_qa_approver',
            'status'               => 'careplan_status',
            'qa_date'              => 'careplan_qa_date',
            'provider_date'        => 'careplan_provider_date',
            'last_printed'         => 'careplan_last_printed',
        ];

        PatientInfo::withTrashed()
            ->where('careplan_provider_approver', '=', 0)
            ->update([
                'careplan_provider_approver' => null,
            ]);

        try {
            foreach (CarePlan::all() as $cp) {
                $patient = PatientInfo::where('user_id', '=', $cp->patient_id)
                    ->first();

                if (!$patient) {
                    continue;
                }

                foreach ($patientInfoToCarePlanMap as $carePlanKey => $patientInfoKey) {
                    if ($patient->$patientInfoKey) {
                        $cp->$carePlanKey = $patient->$patientInfoKey;
                    }
                    $patient->care_plan_id = $cp->id;
                }

                $cp->save();
                $patient->save();
            }
        } catch (\Exception $e) {
            Log::alert('Unable to migrate patient info with ID:' . $patient->id);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
