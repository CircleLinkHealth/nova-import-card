<?php

use CircleLinkHealth\Customer\Entities\Practice;
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

        $practicesToSendTo = [
            'carolina-medical-associates'         => '+17046664445',
            'clinicalosangeles'                   => '+17272050515',
            'elmwood'                             => '+18568326269',
            'tabernacle'                          => '+16094003030',
            'envision'                            => '+12488003080',
            'mazhar'                              => '+19724026269',
            'montgomery'                          => '+18886958537',
            'nestor'                              => '+19174777065',
            'rocky-mountain-health-centers-south' => '+17206050215',
            'upg'                                 => '+17186827501',
            'urgent-medical-care-pc'              => '+19174777065',
            'care-medica'                         => '+12036016075'
        ];

        foreach ($practicesToSendTo as $key => $value) {
            $practice = Practice::whereName($key)->first();

            if ($practice != null) {
                $practice->outgoing_phone_number = $value;
                $practice->save();
            }
        }
    }
}
