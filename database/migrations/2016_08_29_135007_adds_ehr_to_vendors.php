<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddsEhrToVendors extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ccd_vendors', function (Blueprint $table) {
            $table->string('ehr_name')->after('vendor_name')->nullable();
            $table->string('practice_id')->after('ehr_name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ccd_vendors', function (Blueprint $table) {
            $table->dropColumn('ehr_name');
            $table->dropColumn('practice_id');
        });
    }
}
