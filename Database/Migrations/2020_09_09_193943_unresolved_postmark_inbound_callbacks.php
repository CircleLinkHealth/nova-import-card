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
        Schema::drop('unresolved_postmark_inbound_callbacks');
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
            $table->unsignedBigInteger('postmark_rec_id');
            $table->json('suggestions');
            $table->timestamps();

            $table->foreign('postmark_rec_id')
                ->references('id')
                ->on('postmark_inbound_mail')
                ->onUpdate('cascade');
        });
    }
}
