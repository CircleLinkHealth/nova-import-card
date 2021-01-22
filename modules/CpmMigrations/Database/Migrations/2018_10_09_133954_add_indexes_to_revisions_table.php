<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndexesToRevisionsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('revisions', function (Blueprint $table) {
            try {
                $key = 'ops_dashboard_query';

                $table->dropIndex($key);
            } catch (QueryException $e) {
                //                    @todo:heroku review error code below

                $errorCode = $e->errorInfo[1];
                if (1091 == $errorCode) {
                    Log::debug("Key `${key}` does not exist. Nothing to delete.".__FILE__);
                }
            }
        });
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('revisions', function (Blueprint $table) {
            $table->index(['revisionable_id', 'revisionable_type', 'key', 'created_at'], 'ops_dashboard_query');
        });
    }
}
