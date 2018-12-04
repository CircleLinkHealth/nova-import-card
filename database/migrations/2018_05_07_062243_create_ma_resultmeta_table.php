<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateMaResultmetaTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ma_resultmeta', function (Blueprint $table) {
            $table->bigInteger('meta_id', true)->unsigned();
            $table->bigInteger('res_id')->unsigned()->default(0)->index('obs_id');
            $table->string('meta_key')->nullable()->index('ma_result_meta_key');
            $table->text('meta_value')->nullable();
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('ma_resultmeta');
    }
}
