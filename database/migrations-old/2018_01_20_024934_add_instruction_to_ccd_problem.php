<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddInstructionToCcdProblem extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {   
        Schema::table('ccd_problems', function (Blueprint $table) {
            $table->integer('cpm_instruction_id')
                ->nullable()
                ->comment('A pointer to an instruction for the ccd problem')
                ->after('cpm_problem_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ccd_problems', function (Blueprint $table) {
            $table->dropColumn('cpm_instruction_id');
        });
    }
}
