<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEligibilityBatchesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('eligibility_batches', function (Blueprint $table) {
            $table->increments('id');
            $table->string('type');
            $table->integer('status');
            $table->json('options');
            $table->timestamps();
        });

        Schema::table('enrollees', function(Blueprint $table) {
            $table->unsignedInteger('batch_id')
                ->nullable()
                ->after('id');

            $table->foreign('batch_id')
                ->references('id')
                ->on('eligibility_batches')
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
        Schema::table('enrollees', function(Blueprint $table) {
            $table->dropForeign(['batch_id']);
            $table->dropColumn('batch_id');
        });

        Schema::dropIfExists('eligibility_batches');
    }
}
