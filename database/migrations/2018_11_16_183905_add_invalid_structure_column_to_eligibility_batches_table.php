<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddInvalidStructureColumnToEligibilityBatchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('eligibility_batches', function (Blueprint $table) {
            $table->integer('invalid_data')->default(0);
            $table->integer('invalid_structure')->default(0);
            $table->integer('invalid_mrn')->default(0);
            $table->integer('invalid_first_name')->default(0);
            $table->integer('invalid_last_name')->default(0);
            $table->integer('invalid_dob')->default(0);
            $table->integer('invalid_problems')->default(0);
            $table->integer('invalid_phones')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('eligibility_batches', function (Blueprint $table) {
            $table->dropColumn('invalid_data');
            $table->dropColumn('invalid_structure');
            $table->dropColumn('invalid_mrn');
            $table->dropColumn('invalid_first_name');
            $table->dropColumn('invalid_last_name');
            $table->dropColumn('invalid_dob');
            $table->dropColumn('invalid_problems');
            $table->dropColumn('invalid_phones');
        });
    }
}
