<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CerberusSetupTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return  void
     */
    public function up()
    {
        /**
         * This method has been modified to only include adding timestamps to the table.
         */
        Schema::table('lv_permission_role', function (Blueprint $table) {
            if ( ! Schema::hasColumns('lv_permission_role', ['created_at', 'updated_at',])) {
                $table->timestamps();
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return  void
     */
    public function down()
    {
        //
    }
}
