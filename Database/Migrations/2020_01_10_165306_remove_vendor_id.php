<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RemoveVendorId extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('demographics_imports', function (Blueprint $table) {
        });
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('demographics_imports', 'vendor_id')) {
            try {
                Schema::table('demographics_imports', function (Blueprint $table) {
                    $table->dropForeign(['vendor_id']);
                });
            } catch (\Exception $exception) {
            }

            Schema::table('demographics_imports', function (Blueprint $table) {
                $table->dropColumn('vendor_id');
            });
        }
    }
}
