<?php

use App\Practice;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddsEhrsToSomePractises extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $upg = Practice::whereName('upg')->first();
        $upg->ehr_id = 1;
        $upg->save();

        $mazhar = Practice::whereName('mazhar')->first();
        $mazhar->ehr_id = 2;
        $mazhar->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('practices', function (Blueprint $table) {
            //
        });
    }
}
