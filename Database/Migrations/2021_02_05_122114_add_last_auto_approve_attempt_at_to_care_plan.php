<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLastAutoApproveAttemptAtToCarePlan extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('care_plans', function (Blueprint $table) {
            $table->timestamp('last_auto_qa_attempt_at')->nullable();
            $table->timestamp('drafted_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('care_plan', function (Blueprint $table) {
            $table->dropColumn('last_auto_qa_attempt_at');
            $table->dropColumn('drafted_at');
        });
    }
}
