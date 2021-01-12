<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSourceColumnOnEnrolleesTable extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('enrollees', 'source')) {
            Schema::table('enrollees', function (Blueprint $table) {
                $table->dropColumn('source');
            });
        }
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if ( ! Schema::hasColumn('enrollees', 'source')) {
            Schema::table('enrollees', function (Blueprint $table) {
                $table->string('source')->nullable()->after('status');
            });
        }
    }
}
