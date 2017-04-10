<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddImportablesToTmr extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tabular_medical_records', function (Blueprint $table) {
            $table->string('allergies')->after('dob')->nullable();
            $table->string('medications')->after('dob')->nullable();
            $table->string('problems')->after('dob')->nullable();
            $table->string('tertiary_insurance')->after('secondary_insurance')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tabular_medical_records', function (Blueprint $table) {
            $table->dropColumn('allergies');
            $table->dropColumn('medications');
            $table->dropColumn('problems');
            $table->dropColumn('tertiary_insurance');
        });
    }
}
