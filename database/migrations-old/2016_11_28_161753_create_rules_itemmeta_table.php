<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateRulesItemmetaTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rules_itemmeta', function (Blueprint $table) {
            $table->bigInteger('itemmeta_id', true);
            $table->bigInteger('items_id')->nullable()->index('items_id');
            $table->string('meta_key')->nullable()->index('meta_key');
            $table->text('meta_value')->nullable();
            $table->index([
                'items_id',
                'meta_key',
            ], 'items_meta');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('rules_itemmeta');
    }
}
