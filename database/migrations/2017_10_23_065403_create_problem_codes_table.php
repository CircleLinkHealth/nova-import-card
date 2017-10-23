<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProblemCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('problem_codes', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('problem_id');
            $table->string('code_system_name', 20);
            $table->string('code_system_oid', 50);
            $table->string('code', 20);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('problem_id')
                ->references('id')
                ->on('ccd_problems')
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
        Schema::dropIfExists('problem_codes');
    }
}
