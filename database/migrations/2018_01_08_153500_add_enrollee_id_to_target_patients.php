<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEnrolleeIdToTargetPatients extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('target_patients', function (Blueprint $table) {
            $table->unsignedInteger('enrollee_id')
                  ->nullable()
                  ->after('user_id');

            $table->foreign('enrollee_id')
                ->references('id')
                ->on('enrollees')
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
        Schema::table('target_patients', function (Blueprint $table) {
            $table->dropForeign(['enrollee_id']);
            $table->dropColumn('enrollee_id');
        });
    }
}
