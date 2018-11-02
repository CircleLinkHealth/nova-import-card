<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameTernaryInsuranceToTertiary extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('eligibility_jobs', 'ternary_insurance')) {
            Schema::table('eligibility_jobs', function (Blueprint $table) {
                $table->renameColumn('ternary_insurance', 'tertiary_insurance');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('eligibility_jobs', function (Blueprint $table) {
            //
        });
    }
}
