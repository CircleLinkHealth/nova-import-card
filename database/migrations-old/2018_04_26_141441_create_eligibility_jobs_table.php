<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEligibilityJobsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('eligibility_jobs', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('batch_id');
            $table->string('hash', 100)->nullable();
            $table->integer('status')->nullable();
            $table->json('data');
            $table->string('outcome', 20)->nullable();
            $table->json('messages');

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('batch_id')
                  ->references('id')
                  ->on('eligibility_batches')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->index('outcome');
            $table->index('status');
            $table->index('hash');
        });

        if ( ! Schema::hasColumn('eligibility_batches', 'initiator_id')) {
            Schema::table('eligibility_batches', function (Blueprint $table) {
                $table->unsignedInteger('initiator_id')->after('id')->nullable();
                $table->softDeletes();

                $table->foreign('initiator_id')
                      ->references('id')
                      ->on('users')
                      ->onUpdate('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('eligibility_jobs');
    }
}
