<?php

use Illuminate\Database\Seeder;

class CcdVendorsTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('ccd_vendors')->delete();
        
        \DB::table('ccd_vendors')->insert(array (
            0 => 
            array (
                'id' => 1,
                'ccd_import_routine_id' => 1,
                'ehr_name' => 'Aprima',
                'ehr_oid' => 638,
                'doctor_name' => NULL,
                'doctor_oid' => NULL,
                'custodian_name' => NULL,
                'created_at' => '0000-00-00 00:00:00',
                'updated_at' => '0000-00-00 00:00:00',
            ),
            1 => 
            array (
                'id' => 2,
                'ccd_import_routine_id' => 2,
                'ehr_name' => 'STI',
                'ehr_oid' => 929,
                'doctor_name' => NULL,
                'doctor_oid' => NULL,
                'custodian_name' => NULL,
                'created_at' => '0000-00-00 00:00:00',
                'updated_at' => '0000-00-00 00:00:00',
            ),
        ));
        
        
    }
}
