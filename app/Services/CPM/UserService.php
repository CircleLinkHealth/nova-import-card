<?php namespace App\Services\CPM;
use App\CarePlanTemplate;
use App\User;

/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 4/28/16
 * Time: 5:43 PM
 */
class UserService
{
    public function firstOrDefaultCarePlan(User $user = null)
    {
        return $user->patientCarePlans()->firstOrCreate([
            'patient_id' => $user->ID,
            'care_plan_template_id' => CarePlanTemplate::whereType(CarePlanTemplate::CLH_DEFAULT)->first()->id
        ]);
    }
}