<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class UnresolvedPostmarkCallbacks extends Migration
{
    use SoftDeletes;

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('unresolved_postmark_callbacks');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('unresolved_postmark_callbacks', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedBigInteger('postmark_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->json('suggestions')->nullable();
            $table->json('unresolved_reason')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('postmark_id')
                ->references('id')
                ->on('postmark_inbound_mail')
                ->onDelete('cascade');
        });
    }
}
