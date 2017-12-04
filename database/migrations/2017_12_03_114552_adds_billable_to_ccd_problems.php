<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddsBillableToCcdProblems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ccd_problems', function (Blueprint $table) {
            $table->boolean('billable')
                ->after('activate')
                ->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ccd_problems', function (Blueprint $table) {
            $table->dropColumn('billable');
        });
    }
}
