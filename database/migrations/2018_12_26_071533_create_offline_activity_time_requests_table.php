<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOfflineActivityTimeRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'offline_activity_time_requests',
            function (Blueprint $table) {
                $table->increments('id');
                $table->boolean('is_approved')
                      ->nullable();
                $table->boolean('is_behavioral');
                $table->string('type')
                      ->nullable();
                $table->integer('duration_seconds')
                      ->unsigned();
                $table->integer('patient_id')
                      ->unsigned();
                $table->integer('requester_id')
                      ->unsigned();
                $table->integer('activity_id')
                      ->nullable()
                      ->unsigned();
                $table->dateTime('performed_at')
                      ->nullable();
                $table->longText('comment')->nullable();
                $table->timestamps();
                $table->softDeletes();
                
                $table->foreign('patient_id')
                      ->references('id')
                      ->on('users')
                      ->onUpdate('cascade');
                
                $table->foreign('requester_id')
                      ->references('id')
                      ->on('users')
                      ->onUpdate('cascade');
                
                $table->foreign('activity_id')
                      ->references('id')
                      ->on('lv_activities')
                      ->onUpdate('cascade');
            }
        );
    }
    
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('offline_activity_time_requests');
    }
}
