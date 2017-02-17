<?php

use Illuminate\Database\Seeder;

class PopulateTwilioSMSToPractices extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $clinica = \App\Practice::whereName('clinicalosangeles')->first();

        if($clinica != null){
            $clinica->sms_marketing_number = '+17272050515';
            $clinica->save();
        }

        $river_city = \App\Practice::whereName('river-city')->first();

        if($river_city != null){
            $river_city->sms_marketing_number = '+19162490619';
            $river_city->save();
        }

        $quest = \App\Practice::whereName('quest-medical-care-pc')->first();

        if($quest != null){
            $quest->sms_marketing_number = '+16312120015 ';
            $quest->save();
        }

    }
}
