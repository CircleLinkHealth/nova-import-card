<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCcdAllergyLogsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ccd_allergy_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('medical_record_type')->nullable();
            $table->integer('medical_record_id')->unsigned()->nullable();
            $table->integer('vendor_id')->unsigned()->nullable()->index('ccd_allergy_logs_vendor_id_foreign');
            $table->string('start')->nullable();
            $table->string('end')->nullable();
            $table->string('status')->nullable();
            $table->string('allergen_name')->nullable();
            $table->boolean('import');
            $table->boolean('invalid');
            $table->boolean('edited');
            $table->softDeletes();
            $table->timestamps();
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('ccd_allergy_logs');
    }
}
