<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMaFitbitNotificationsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ma_fitbit_notifications', function (Blueprint $table) {
            $table->bigInteger('ID', true);
            $table->string('device', 30);
            $table->text('content', 65535)->nullable();
            $table->boolean('process_status')->default(0);
            $table->timestamp('last_updated')->default(DB::raw('CURRENT_TIMESTAMP'));
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('ma_fitbit_notifications');
    }
}
