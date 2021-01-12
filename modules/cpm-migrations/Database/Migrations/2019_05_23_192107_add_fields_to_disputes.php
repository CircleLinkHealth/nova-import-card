<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddFieldsToDisputes extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('disputes', function (Blueprint $table) {
        });
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('disputes', function (Blueprint $table) {
            if ( ! Schema::hasColumn('disputes', 'resolved_by')) {
                $table->unsignedInteger('resolved_by')->after('resolved_at')->nullable();
            }

            if ( ! Schema::hasColumn('disputes', 'disputable_id')) {
                $table->unsignedInteger('disputable_id')->after('id');
            }

            if ( ! Schema::hasColumn('disputes', 'disputable_type')) {
                $table->string('disputable_type')->after('id');
            }

            $table->foreign('resolved_by')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade');
        });
    }
}
