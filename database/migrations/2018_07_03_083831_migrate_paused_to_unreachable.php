<?php

use App\Patient;
use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;

class MigratePausedToUnreachable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Patient::query()
               ->where('date_paused', '<', Carbon::createFromDate(2018, 5, 1))
               ->get()
               ->each(function ($p) {
                   $p->ccm_status       = Patient::UNREACHABLE;
                   $p->date_unreachable = $p->date_paused;
                   $p->save();
               });
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
