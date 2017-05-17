<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenameTabDataFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('tabular_medical_records', function (Blueprint $table) {
            $table->renameColumn('allergies', 'allergies_string');
            $table->renameColumn('medications', 'medications_string');
            $table->renameColumn('problems', 'problems_string');
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
            //
        });
    }
}
