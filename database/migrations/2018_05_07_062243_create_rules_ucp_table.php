<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateRulesUcpTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('rules_ucp');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('rules_ucp', function (Blueprint $table) {
            $table->bigInteger('ucp_id', true);
            $table->bigInteger('items_id')->nullable();
            $table->bigInteger('user_id')->nullable();
            $table->string('meta_key')->nullable();
            $table->text('meta_value')->nullable();
            $table->unique(['items_id', 'user_id', 'meta_key'], 'rules_ucp_meta');
        });
    }
}
