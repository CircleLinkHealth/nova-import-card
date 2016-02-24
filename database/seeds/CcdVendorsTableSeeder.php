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
                'vendor_name' => 'Aprima - UPG',
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
                'vendor_name' => 'STI - Tabernacle',
                'ehr_oid' => 929,
                'doctor_name' => NULL,
                'doctor_oid' => NULL,
                'custodian_name' => NULL,
                'created_at' => '0000-00-00 00:00:00',
                'updated_at' => '0000-00-00 00:00:00',
            ),
            2 => 
            array (
                'id' => 3,
                'ccd_import_routine_id' => 3,
                'vendor_name' => 'Athena - Mazhar',
                'ehr_oid' => 564,
                'doctor_name' => 'Salma Mazhar',
                'doctor_oid' => 1487690855,
                'custodian_name' => 'athenahealth',
                'created_at' => '0000-00-00 00:00:00',
                'updated_at' => '0000-00-00 00:00:00',
            ),
            3 => 
            array (
                'id' => 5,
                'ccd_import_routine_id' => 4,
                'vendor_name' => 'WRS - Purser',
                'ehr_oid' => NULL,
                'doctor_name' => 'Constance Purser',
                'doctor_oid' => NULL,
                'custodian_name' => 'Constance B. Purser, M.D.',
                'created_at' => '0000-00-00 00:00:00',
                'updated_at' => '0000-00-00 00:00:00',
            ),
            4 => 
            array (
                'id' => 6,
                'ccd_import_routine_id' => 6,
                'vendor_name' => 'WRS - Nestor',
                'ehr_oid' => NULL,
                'doctor_name' => 'Gregory Nestor',
                'doctor_oid' => NULL,
                'custodian_name' => 'Dr. Gregory Nestor, MD.,LLC',
                'created_at' => '0000-00-00 00:00:00',
                'updated_at' => '0000-00-00 00:00:00',
            ),
            5 => 
            array (
                'id' => 7,
                'ccd_import_routine_id' => 2,
                'vendor_name' => 'STI - Elmwood',
                'ehr_oid' => NULL,
                'doctor_name' => NULL,
                'doctor_oid' => NULL,
                'custodian_name' => NULL,
                'created_at' => '0000-00-00 00:00:00',
                'updated_at' => '0000-00-00 00:00:00',
            ),
            6 => 
            array (
                'id' => 9,
                'ccd_import_routine_id' => 5,
                'vendor_name' => 'Prognosis - Middletown',
                'ehr_oid' => NULL,
                'doctor_name' => 'Trish Hewston',
                'doctor_oid' => NULL,
                'custodian_name' => 'Middletown ',
                'created_at' => '0000-00-00 00:00:00',
                'updated_at' => '0000-00-00 00:00:00',
            ),
        ));
        
        
    }
}
