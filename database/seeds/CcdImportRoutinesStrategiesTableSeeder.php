<?php

use Illuminate\Database\Seeder;

class CcdImportRoutinesStrategiesTableSeeder extends Seeder
{

    /**
     * Auto generated seed file
     *
     * @return void
     */
    public function run()
    {
        

        \DB::table('ccd_import_routines_strategies')->delete();
        
        \DB::table('ccd_import_routines_strategies')->insert(array (
            0 => 
            array (
                'id' => 1,
                'ccd_import_routine_id' => 1,
                'importer_section_id' => 0,
                'validator_id' => 3,
                'parser_id' => 0,
                'storage_id' => 0,
                'created_at' => '0000-00-00 00:00:00',
                'updated_at' => '0000-00-00 00:00:00',
            ),
            1 => 
            array (
                'id' => 2,
                'ccd_import_routine_id' => 1,
                'importer_section_id' => 1,
                'validator_id' => 3,
                'parser_id' => 1,
                'storage_id' => 2,
                'created_at' => '0000-00-00 00:00:00',
                'updated_at' => '0000-00-00 00:00:00',
            ),
            2 => 
            array (
                'id' => 3,
                'ccd_import_routine_id' => 1,
                'importer_section_id' => 2,
                'validator_id' => 3,
                'parser_id' => 2,
                'storage_id' => 3,
                'created_at' => '0000-00-00 00:00:00',
                'updated_at' => '0000-00-00 00:00:00',
            ),
            3 => 
            array (
                'id' => 4,
                'ccd_import_routine_id' => 1,
                'importer_section_id' => 3,
                'validator_id' => 3,
                'parser_id' => 3,
                'storage_id' => 1,
                'created_at' => '0000-00-00 00:00:00',
                'updated_at' => '0000-00-00 00:00:00',
            ),
            4 => 
            array (
                'id' => 5,
                'ccd_import_routine_id' => 2,
                'importer_section_id' => 0,
                'validator_id' => 2,
                'parser_id' => 0,
                'storage_id' => 0,
                'created_at' => '0000-00-00 00:00:00',
                'updated_at' => '0000-00-00 00:00:00',
            ),
            5 => 
            array (
                'id' => 6,
                'ccd_import_routine_id' => 2,
                'importer_section_id' => 1,
                'validator_id' => 2,
                'parser_id' => 1,
                'storage_id' => 2,
                'created_at' => '0000-00-00 00:00:00',
                'updated_at' => '0000-00-00 00:00:00',
            ),
            6 => 
            array (
                'id' => 7,
                'ccd_import_routine_id' => 2,
                'importer_section_id' => 2,
                'validator_id' => 2,
                'parser_id' => 2,
                'storage_id' => 3,
                'created_at' => '0000-00-00 00:00:00',
                'updated_at' => '0000-00-00 00:00:00',
            ),
            7 => 
            array (
                'id' => 8,
                'ccd_import_routine_id' => 2,
                'importer_section_id' => 3,
                'validator_id' => 2,
                'parser_id' => 3,
                'storage_id' => 1,
                'created_at' => '0000-00-00 00:00:00',
                'updated_at' => '0000-00-00 00:00:00',
            ),
            8 => 
            array (
                'id' => 9,
                'ccd_import_routine_id' => 3,
                'importer_section_id' => 0,
                'validator_id' => 3,
                'parser_id' => 0,
                'storage_id' => 0,
                'created_at' => '0000-00-00 00:00:00',
                'updated_at' => '0000-00-00 00:00:00',
            ),
            9 => 
            array (
                'id' => 10,
                'ccd_import_routine_id' => 3,
                'importer_section_id' => 1,
                'validator_id' => 3,
                'parser_id' => 1,
                'storage_id' => 2,
                'created_at' => '0000-00-00 00:00:00',
                'updated_at' => '0000-00-00 00:00:00',
            ),
            10 => 
            array (
                'id' => 12,
                'ccd_import_routine_id' => 3,
                'importer_section_id' => 2,
                'validator_id' => 3,
                'parser_id' => 2,
                'storage_id' => 3,
                'created_at' => '0000-00-00 00:00:00',
                'updated_at' => '0000-00-00 00:00:00',
            ),
            11 => 
            array (
                'id' => 13,
                'ccd_import_routine_id' => 3,
                'importer_section_id' => 3,
                'validator_id' => 0,
                'parser_id' => 4,
                'storage_id' => 1,
                'created_at' => '0000-00-00 00:00:00',
                'updated_at' => '0000-00-00 00:00:00',
            ),
            12 => 
            array (
                'id' => 14,
                'ccd_import_routine_id' => 4,
                'importer_section_id' => 0,
                'validator_id' => 0,
                'parser_id' => 0,
                'storage_id' => 0,
                'created_at' => '0000-00-00 00:00:00',
                'updated_at' => '0000-00-00 00:00:00',
            ),
            13 => 
            array (
                'id' => 16,
                'ccd_import_routine_id' => 4,
                'importer_section_id' => 1,
                'validator_id' => 0,
                'parser_id' => 0,
                'storage_id' => 0,
                'created_at' => '0000-00-00 00:00:00',
                'updated_at' => '0000-00-00 00:00:00',
            ),
            14 => 
            array (
                'id' => 17,
                'ccd_import_routine_id' => 4,
                'importer_section_id' => 2,
                'validator_id' => 0,
                'parser_id' => 0,
                'storage_id' => 0,
                'created_at' => '0000-00-00 00:00:00',
                'updated_at' => '0000-00-00 00:00:00',
            ),
            15 => 
            array (
                'id' => 18,
                'ccd_import_routine_id' => 4,
                'importer_section_id' => 3,
                'validator_id' => 1,
                'parser_id' => 3,
                'storage_id' => 1,
                'created_at' => '0000-00-00 00:00:00',
                'updated_at' => '0000-00-00 00:00:00',
            ),
            16 => 
            array (
                'id' => 22,
                'ccd_import_routine_id' => 5,
                'importer_section_id' => 0,
                'validator_id' => 0,
                'parser_id' => 0,
                'storage_id' => 0,
                'created_at' => '0000-00-00 00:00:00',
                'updated_at' => '0000-00-00 00:00:00',
            ),
            17 => 
            array (
                'id' => 24,
                'ccd_import_routine_id' => 5,
                'importer_section_id' => 1,
                'validator_id' => 0,
                'parser_id' => 1,
                'storage_id' => 2,
                'created_at' => '0000-00-00 00:00:00',
                'updated_at' => '0000-00-00 00:00:00',
            ),
            18 => 
            array (
                'id' => 25,
                'ccd_import_routine_id' => 5,
                'importer_section_id' => 2,
                'validator_id' => 0,
                'parser_id' => 2,
                'storage_id' => 3,
                'created_at' => '0000-00-00 00:00:00',
                'updated_at' => '0000-00-00 00:00:00',
            ),
            19 => 
            array (
                'id' => 26,
                'ccd_import_routine_id' => 5,
                'importer_section_id' => 3,
                'validator_id' => 0,
                'parser_id' => 3,
                'storage_id' => 1,
                'created_at' => '0000-00-00 00:00:00',
                'updated_at' => '0000-00-00 00:00:00',
            ),
            20 => 
            array (
                'id' => 27,
                'ccd_import_routine_id' => 6,
                'importer_section_id' => 0,
                'validator_id' => 0,
                'parser_id' => 0,
                'storage_id' => 0,
                'created_at' => '0000-00-00 00:00:00',
                'updated_at' => '0000-00-00 00:00:00',
            ),
            21 => 
            array (
                'id' => 28,
                'ccd_import_routine_id' => 6,
                'importer_section_id' => 1,
                'validator_id' => 0,
                'parser_id' => 1,
                'storage_id' => 2,
                'created_at' => '0000-00-00 00:00:00',
                'updated_at' => '0000-00-00 00:00:00',
            ),
            22 => 
            array (
                'id' => 29,
                'ccd_import_routine_id' => 6,
                'importer_section_id' => 2,
                'validator_id' => 0,
                'parser_id' => 2,
                'storage_id' => 3,
                'created_at' => '0000-00-00 00:00:00',
                'updated_at' => '0000-00-00 00:00:00',
            ),
            23 => 
            array (
                'id' => 30,
                'ccd_import_routine_id' => 6,
                'importer_section_id' => 3,
                'validator_id' => 4,
                'parser_id' => 3,
                'storage_id' => 1,
                'created_at' => '0000-00-00 00:00:00',
                'updated_at' => '0000-00-00 00:00:00',
            ),
        ));
        
        
    }
}
