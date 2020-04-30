<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class OptimizeSlowRevisionableQueries extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        try {
            Schema::table('revisions', function (Blueprint $table) {
                $table->index('updated_at');
                $table->index(['updated_at', 'is_phi']);
            });
        }
        catch (Exception $e) {
            //in case index already exists
        }
        
    }
}
