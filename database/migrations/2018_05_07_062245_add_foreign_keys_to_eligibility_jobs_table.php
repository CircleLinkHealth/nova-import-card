<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToEligibilityJobsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('eligibility_jobs', function (Blueprint $table) {
            $table->foreign('batch_id')->references('id')->on('eligibility_batches')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('eligibility_jobs', function (Blueprint $table) {
            $table->dropForeign('eligibility_jobs_batch_id_foreign');
        });
    }
}
