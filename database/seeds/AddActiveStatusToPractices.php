<?php

use Illuminate\Database\Seeder;

class AddActiveStatusToPractices extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $practices = [
            'elmwood',
            'mazhar',
            'nestor',
            'upg',
            'tabernacle',
            'montgomery',
            'middletownmedical',
            'carolina-medical-associates',
            'envision',
            'clinicalosangeles',
            'rocky-mountain-health-centers-south',
            'quest-medical-care-pc',
            'urgent-medical-care-pc',
            'river-city',
            'premier-heart-and-vein-care',
            'care-medica',
            'ferguson-medical',
            'CCN General Medicine'
        ];

        foreach ($practices as $practiceName) {
            $practice = \App\Practice::whereName($practiceName)->first();

            if ($practice != null) {
                $practice->active = 1;
                $practice->save();
            }
        }
    }
}
