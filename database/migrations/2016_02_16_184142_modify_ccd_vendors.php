<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyCcdVendors extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table( 'ccd_vendors', function (Blueprint $table) {
            if ( Schema::hasColumn( 'ccd_vendors', 'ehr_name' ) ) {
                $table->renameColumn( 'ehr_name', 'vendor_name' );
            }
        } );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table( 'ccd_vendors', function (Blueprint $table) {
            //
        } );
    }

}
