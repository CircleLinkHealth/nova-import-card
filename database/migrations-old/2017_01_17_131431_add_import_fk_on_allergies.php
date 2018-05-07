<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddImportFkOnAllergies extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ccd_allergies', function (Blueprint $table) {
            $table->unsignedInteger('allergy_import_id')
                ->nullable()
                ->after('id');

            $table->foreign('allergy_import_id')
                ->references('id')
                ->on('allergy_imports')
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
        Schema::table('ccd_allergies', function (Blueprint $table) {
            //
        });
    }
}
