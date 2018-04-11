<?php

use App\Models\PatientData\PhoenixHeart\PhoenixHeartName;
use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class NormalizePHXDates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
//        PhoenixHeartName::chunk(500, function ($rows) {
//            foreach ($rows as $row) {
//                $row->dob = Carbon::parse($row->dob)->toDateString();
//                $row->save();
//            }
//        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
