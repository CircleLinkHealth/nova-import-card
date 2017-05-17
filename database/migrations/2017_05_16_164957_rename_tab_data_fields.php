<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $table->renameColumn('allergies_string', 'allergies');
            $table->renameColumn('medications_string', 'medications');
            $table->renameColumn('problems_string', 'problems');
        });
    }
}
