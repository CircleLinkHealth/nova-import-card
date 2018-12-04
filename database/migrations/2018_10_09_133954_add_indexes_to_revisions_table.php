<?php

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndexesToRevisionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('revisions', function (Blueprint $table) {
            $table->index(['revisionable_id', 'revisionable_type', 'key', 'created_at'], 'ops_dashboard_query');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('revisions', function (Blueprint $table) {
            try {
                $key = 'ops_dashboard_query';

                $table->dropIndex($key);

            } catch (QueryException $e) {
                $errorCode = $e->errorInfo[1];
                if ($errorCode == 1091) {
                    Log::debug("Key `$key` does not exist. Nothing to delete." . __FILE__);
                }
            }
        });
    }
}
