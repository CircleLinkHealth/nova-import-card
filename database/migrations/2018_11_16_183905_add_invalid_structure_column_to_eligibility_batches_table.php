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
            $table->integer('invalid_structure')->after('stats')->default(0);
            $table->json('validation_stats')->after('invalid_structure')->nullable();
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
            $table->dropColumn('invalid_structure');
            $table->dropColumn('validation_stats');
        });
    }
}
