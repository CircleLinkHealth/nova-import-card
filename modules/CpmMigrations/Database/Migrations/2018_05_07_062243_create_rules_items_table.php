<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateRulesItemsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('rules_items');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('rules_items', function (Blueprint $table) {
            $table->bigInteger('items_id', true);
            $table->bigInteger('pcp_id')->nullable();
            $table->bigInteger('items_parent')->nullable()->default(0);
            $table->bigInteger('qid')->nullable()->index('idx_qid');
            $table->string('care_item_id');
            $table->string('name');
            $table->string('display_name');
            $table->string('description');
            $table->string('items_text', 254)->nullable();
            $table->softDeletes();
        });
    }
}
