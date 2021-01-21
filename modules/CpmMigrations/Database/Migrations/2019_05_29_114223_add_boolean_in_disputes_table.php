<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBooleanInDisputesTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('disputes');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        if (Schema::hasTable('disputes')) {
            Schema::table('disputes', function (Blueprint $table) {
                $table->boolean('is_resolved')->default(false);
            });
        }
    }
}
