<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddImportFkOnCcdMedications extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ccd_medications', function (Blueprint $table) {
            $table->unsignedInteger('medication_import_id')
                ->nullable()
                ->after('id');

            $table->foreign('medication_import_id')
                ->references('id')
                ->on('medication_imports')
                ->onUpdate('cascade')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ccd_medications', function (Blueprint $table) {
            //
        });
    }
}
