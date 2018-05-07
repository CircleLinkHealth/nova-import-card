<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddMedicationLogId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('medication_imports', function (Blueprint $table) {
            $table->unsignedInteger('medication_group_id')->nullable()->after('ccd_medication_log_id');

            $table->foreign('medication_group_id')
                ->references('id')
                ->on('cpm_medication_groups')
                ->onDelete('cascade')
                ->onUpdate('cascade');
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
            $table->dropForeign(['medication_group_id']);
            $table->dropColumn('medication_group_id');
        });
    }
}
