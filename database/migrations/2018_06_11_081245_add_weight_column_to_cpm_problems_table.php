<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddWeightColumnToCpmProblemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cpm_problems', function (Blueprint $table) {
            $table->integer('weight')->default(1)->after('is_behavioral');
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
            $table->dropColumn('weight');
        });
    }
}
