<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateRulesPcpTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rules_pcp', function (Blueprint $table) {
            $table->bigInteger('pcp_id', true);
            $table->bigInteger('prov_id')->nullable();
            $table->string('section_text', 254)->nullable();
            $table->string('status', 45)->nullable();
            $table->integer('cpset_id')->nullable();
            $table->string('pcp_type', 45)->nullable();
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('rules_pcp');
    }
}
