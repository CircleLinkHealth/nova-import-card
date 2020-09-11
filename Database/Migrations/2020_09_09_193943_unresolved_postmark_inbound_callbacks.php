<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class UnresolvedPostmarkInboundCallbacks extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('unresolved_postmark_inbound_callbacks', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('postmark_rec_id');
            $table->unsignedInteger('call_id'); // Do i need calls to know communicate with this?
            $table->json('suggestions');
            $table->boolean('resolved_manually');
            $table->string('issue_type')->nullable();
            $table->timestamps();
        });
    }
}
