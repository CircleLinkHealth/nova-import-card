<?php

use App\Program;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class S20160102ProgramColumnMigration extends Seeder
{

    public function run()
    {
        $programs = Program::all();
        echo PHP_EOL.PHP_EOL . 'START' . PHP_EOL.PHP_EOL;
        foreach($programs as $program) {
            echo PHP_EOL.'--Found program ' .$program->blog_id.PHP_EOL.PHP_EOL;
            if(Schema::connection('mysql_no_prefix')->hasTable('wp_' . $program->blog_id . '_options')) {
                $programOptions = DB::connection('mysql_no_prefix')->table('wp_' . $program->blog_id . '_options')->get();
                if (empty($programOptions)) {
                    echo 'No table wp_' . $program->blog_id . '_options found'.PHP_EOL;
                }
                foreach($programOptions as $programOption) {
                    if($programOption->option_name == 'att_config') {
                        $program->att_config = $programOption->option_value;
                        echo 'Updated ' . $program->blog_id . ' att_config'.PHP_EOL;
                    }
                    if($programOption->option_name == 'location_id') {
                        $program->location_id = $programOption->option_value;
                        echo 'Updated ' . $program->blog_id . ' location_id'.PHP_EOL;
                    }
                }
                $program->save();
            }
        }
    }
}