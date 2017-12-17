<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TargetPatient extends Model
{
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }


    static function updateOrCreate($ehrPatientId, $departmentId, $practiceId)
    {
        $targetPatient = TargetPatient::where('ehr_patient_id', $ehrPatientId)
            ->where('department_id', $departmentId)
            ->where('practice_id', $departmentId)
            ->first();

        $ehrId = Practice::find($practiceId)->with('ehr:id')->first();

        if ($targetPatient and !$targetPatient->status){
            $targetPatient->status = 'to_process';
            $targetPatient->save();
        }else{
            $targetPatient->ehr_id = $ehrId;
            $targetPatient->ehr_patient_id = $ehrPatientId;
            $targetPatient->ehr_department_id = $departmentId;
            $targetPatient->ehr_practice_id = $practiceId;
            $targetPatient->status = 'to_process';
            $targetPatient->save();

        }

        return $targetPatient;

    }


}
