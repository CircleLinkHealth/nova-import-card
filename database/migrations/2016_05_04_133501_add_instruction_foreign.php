<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddInstructionForeign extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('instructables', function (Blueprint $table) {
            $table->foreign('cpm_instruction_id')
                ->references('id')
                ->on('cpm_instructions')
                ->onUpdate('cascade')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('instructables', function (Blueprint $table) {
            $table->dropForeign(['cpm_instruction_id']);
        });
    }
}
