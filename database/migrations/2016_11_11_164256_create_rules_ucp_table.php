<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateRulesUcpTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rules_ucp', function (Blueprint $table) {
            $table->bigInteger('ucp_id', true);
            $table->bigInteger('items_id')->nullable();
            $table->bigInteger('user_id')->nullable();
            $table->string('meta_key')->nullable();
            $table->text('meta_value')->nullable();
            $table->unique([
                'items_id',
                'user_id',
                'meta_key',
            ], 'meta');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('rules_ucp');
    }

}
