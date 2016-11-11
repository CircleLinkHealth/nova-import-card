<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCcdasTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ccdas', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->nullable()->index('lv_ccdas_user_id_foreign');
            $table->integer('patient_id')->unsigned()->nullable()->index('users_patient_id_foreign');
            $table->integer('vendor_id')->unsigned()->index('lv_ccdas_vendor_id_foreign');
            $table->string('source');
            $table->boolean('imported');
            $table->text('xml');
            $table->text('json');
            $table->timestamps();
            $table->softDeletes();
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('ccdas');
    }

}
