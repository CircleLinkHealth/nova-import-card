<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDefaultIcd10CodeToCpmProblems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cpm_problems', function (Blueprint $table) {
            $table->string('default_icd_10_code', 20)
                ->after('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cpm_problems', function (Blueprint $table) {
            $table->dropColumn('default_icd_10_code');
        });
    }
}
