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
                'program_id' => 16,
                'ccd_import_routine_id' => 1,
                'vendor_name' => 'UPG',
                'ehr_name' => 'aprima',
                'practice_id' => NULL,
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
                'program_id' => 15,
                'ccd_import_routine_id' => 2,
                'vendor_name' => 'Tabernacle',
                'ehr_name' => 'sti',
                'practice_id' => NULL,
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
                'program_id' => 21,
                'ccd_import_routine_id' => 3,
                'vendor_name' => 'Mazhar',
                'ehr_name' => 'athena',
                'practice_id' => '1959188',
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
                'program_id' => 22,
                'ccd_import_routine_id' => 4,
                'vendor_name' => 'Purser',
                'ehr_name' => 'wrs',
                'practice_id' => NULL,
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
                'program_id' => 20,
                'ccd_import_routine_id' => 4,
                'vendor_name' => 'Nestor',
                'ehr_name' => 'wrs',
                'practice_id' => NULL,
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
                'program_id' => 14,
                'ccd_import_routine_id' => 2,
                'vendor_name' => 'Elmwood',
                'ehr_name' => 'sti',
                'practice_id' => NULL,
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
                'program_id' => 23,
                'ccd_import_routine_id' => 6,
                'vendor_name' => 'Middletown',
                'ehr_name' => 'prognosis',
                'practice_id' => NULL,
                'ehr_oid' => NULL,
                'doctor_name' => 'Trish Hewston',
                'doctor_oid' => NULL,
                'custodian_name' => 'Middletown ',
                'created_at' => '0000-00-00 00:00:00',
                'updated_at' => '0000-00-00 00:00:00',
            ),
            7 => 
            array (
                'id' => 10,
                'program_id' => 25,
                'ccd_import_routine_id' => 5,
                'vendor_name' => 'Carolina Medical Associates',
                'ehr_name' => 'epic',
                'practice_id' => NULL,
                'ehr_oid' => NULL,
                'doctor_name' => NULL,
                'doctor_oid' => NULL,
                'custodian_name' => NULL,
                'created_at' => '0000-00-00 00:00:00',
                'updated_at' => '0000-00-00 00:00:00',
            ),
            8 => 
            array (
                'id' => 11,
                'program_id' => 12,
                'ccd_import_routine_id' => 8,
                'vendor_name' => 'Montgomery Medical',
                'ehr_name' => 'medent',
                'practice_id' => NULL,
                'ehr_oid' => NULL,
                'doctor_name' => NULL,
                'doctor_oid' => NULL,
                'custodian_name' => NULL,
                'created_at' => '0000-00-00 00:00:00',
                'updated_at' => '0000-00-00 00:00:00',
            ),
            9 => 
            array (
                'id' => 12,
                'program_id' => 27,
                'ccd_import_routine_id' => 9,
                'vendor_name' => 'Monheit',
                'ehr_name' => NULL,
                'practice_id' => NULL,
                'ehr_oid' => NULL,
                'doctor_name' => NULL,
                'doctor_oid' => NULL,
                'custodian_name' => NULL,
                'created_at' => '0000-00-00 00:00:00',
                'updated_at' => '0000-00-00 00:00:00',
            ),
            10 => 
            array (
                'id' => 13,
                'program_id' => 28,
                'ccd_import_routine_id' => 1,
                'vendor_name' => 'Envision',
                'ehr_name' => NULL,
                'practice_id' => NULL,
                'ehr_oid' => NULL,
                'doctor_name' => NULL,
                'doctor_oid' => NULL,
                'custodian_name' => NULL,
                'created_at' => '0000-00-00 00:00:00',
                'updated_at' => '0000-00-00 00:00:00',
            ),
            11 => 
            array (
                'id' => 14,
                'program_id' => 8,
                'ccd_import_routine_id' => 1,
                'vendor_name' => 'Demo',
                'ehr_name' => NULL,
                'practice_id' => NULL,
                'ehr_oid' => NULL,
                'doctor_name' => NULL,
                'doctor_oid' => NULL,
                'custodian_name' => NULL,
                'created_at' => '0000-00-00 00:00:00',
                'updated_at' => '0000-00-00 00:00:00',
            ),
        ));
        
        
    }
}
