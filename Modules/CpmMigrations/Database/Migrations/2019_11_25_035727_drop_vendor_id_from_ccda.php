<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class DropVendorIdFromCcda extends Migration
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
        if (Schema::hasColumn('ccdas', 'vendor_id')) {
            Schema::table(
                'ccdas',
                function (Blueprint $table) {
                    $table->dropColumn('vendor_id');
                }
            );
        }
    }
}
