<?php namespace database\seeds;

use App\Activity;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

class ActivityTableSeeder extends Seeder {

    public function run()
    {
        DB::table('lv_activities')->delete();

        Activity::create([
            'type' => 'Care Plan Setup',
            'duration' => 12,
            'duration_unit' => 'minutes',
            'logged_from' => 'ui'
        ]);
    }

}