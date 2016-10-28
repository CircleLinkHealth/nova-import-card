<?php

use App\Location;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class S20151027Locations extends Seeder
{

    public function run()
    {
        $blogIds = \App\Practice::all();

        foreach ($blogIds as $blog) {

            if ($blog->id > 6) {

                $options_table_name = 'wp_' . $blog->id . '_options';

                $loc_id = DB::connection('mysql_no_prefix')->table($options_table_name)->where('option_name', 'location_id')->first();

                if ($loc_id) {

                    $locations_for_blog = App\Location::where('parent_id', $loc_id->option_value)->orWhere('id', $loc_id->option_value)->get();

                    //dd($locations_for_blog);
                    if (count($locations_for_blog) > 0) {
                        foreach ($locations_for_blog as $loc) {
                            $loc->program_id = $blog->id;
                            $loc->save();
                            //echo json_encode($loc);
                        }
                    }
                }
            }
        }
    }
}