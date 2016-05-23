<?php

use Illuminate\Database\Seeder;

class CcdImportRoutinesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('ccd_import_routines')->delete();
        
        \DB::table('ccd_import_routines')->insert(array (
            0 => 
            array (
                'id' => 1,
                'name' => 'Status',
                'description' => 'Import everything using the status.',
                'created_at' => '0000-00-00 00:00:00',
                'updated_at' => '0000-00-00 00:00:00',
            ),
            1 => 
            array (
                'id' => 2,
                'name' => 'Valid Start Date, No End Date',
                'description' => 'Import everything with a valid start date but no end date.',
                'created_at' => '0000-00-00 00:00:00',
                'updated_at' => '0000-00-00 00:00:00',
            ),
            2 => 
            array (
                'id' => 3,
                'name' => 'Import All Meds, Status Problems, Status Allergies',
                'description' => 'Import All Medications, Active Problems and Allergies',
                'created_at' => '0000-00-00 00:00:00',
                'updated_at' => '0000-00-00 00:00:00',
            ),
            3 => 
            array (
                'id' => 4,
                'name' => 'Medications: Status, Everything else: Import all',
                'description' => 'Import Medications using Status, Import all problems and allergies',
                'created_at' => '0000-00-00 00:00:00',
                'updated_at' => '0000-00-00 00:00:00',
            ),
            4 => 
            array (
                'id' => 5,
                'name' => 'Import Everything',
                'description' => 'Import All Meds, All Problems and all Allergies.',
                'created_at' => '0000-00-00 00:00:00',
                'updated_at' => '0000-00-00 00:00:00',
            ),
            5 => 
            array (
                'id' => 6,
                'name' => 'Valid End Date Or Valid Start Date, No End Date',
                'description' => '',
                'created_at' => '0000-00-00 00:00:00',
                'updated_at' => '0000-00-00 00:00:00',
            ),
            6 => 
            array (
                'id' => 7,
                'name' => 'Optional End Date',
                'description' => 'Import all items UNLESS they have an end date that has passed.',
                'created_at' => '0000-00-00 00:00:00',
                'updated_at' => '0000-00-00 00:00:00',
            ),
        ));
        
        
    }
}
