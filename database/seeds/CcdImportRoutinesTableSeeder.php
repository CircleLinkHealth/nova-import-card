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
        ));
        
        
    }
}
