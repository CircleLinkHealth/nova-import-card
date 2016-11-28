<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateCcdProviderLogsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ccd_provider_logs', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('ccda_id')->unsigned()->index('ccd_provider_logs_ccda_id_foreign');
            $table->integer('vendor_id')->unsigned()->index('ccd_provider_logs_vendor_id_foreign');
            $table->string('npi')->nullable();
            $table->string('provider_id')->nullable();
            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('organization')->nullable();
            $table->string('street')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zip', 5)->nullable();
            $table->string('cell_phone', 12)->nullable();
            $table->string('home_phone', 12)->nullable();
            $table->string('work_phone', 12)->nullable();
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
        Schema::drop('ccd_provider_logs');
    }

}
