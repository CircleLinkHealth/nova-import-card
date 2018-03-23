<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class BhiCcdProblems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ccd_problems', function (Blueprint $table) {
            $table->boolean('is_behavioral')->default(false)->after('cpm_instruction_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('ccd_problems', 'is_behavioral')) {
            Schema::table('ccd_problems', function (Blueprint $table) {
                $table->dropColumn('is_behavioral');
            });
        }
    }
}
