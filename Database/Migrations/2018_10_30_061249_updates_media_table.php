<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdatesMediaTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::table('media', function (Blueprint $table) {
            $table->dropColumn('responsive_images');
        });
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('media', function (Blueprint $table) {
            $table->json('responsive_images')
                ->after('custom_properties');
        });
    }
}
