<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToMedicationImportsTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('medication_imports', function (Blueprint $table) {
            $table->foreign('ccd_medication_log_id')->references('id')->on('ccd_medication_logs')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('ccda_id')->references('id')->on('ccdas')->onUpdate('CASCADE')->onDelete('CASCADE');
            $table->foreign('substitute_id')->references('id')->on('medication_imports')->onUpdate('RESTRICT')->onDelete('RESTRICT');
            $table->foreign('vendor_id')->references('id')->on('ccd_vendors')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('medication_imports', function (Blueprint $table) {
            $table->dropForeign('medication_imports_ccd_medication_log_id_foreign');
            $table->dropForeign('medication_imports_ccda_id_foreign');
            $table->dropForeign('medication_imports_substitute_id_foreign');
            $table->dropForeign('medication_imports_vendor_id_foreign');
        });
    }
}
