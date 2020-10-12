<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddManuallyResolvedFieldToUnresolvableCallbacks extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('unresolved_postmark_callbacks', function (Blueprint $table) {
            $table->dropColumn('manually_resolved');
        });
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('unresolved_postmark_callbacks', function (Blueprint $table) {
            $table->boolean('manually_resolved')->default(false);
        });
    }
}
