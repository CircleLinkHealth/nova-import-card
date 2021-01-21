<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePostmarkInboundMailTable extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('postmark_inbound_mail');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('postmark_inbound_mail', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->json('data');

            $table->string('from')
                ->virtualAs('JSON_UNQUOTE(data->"$.From")')
                ->index();

            $table->string('to')
                ->virtualAs('JSON_UNQUOTE(data->"$.To")')
                ->index();

            $table->text('body')
                ->virtualAs('JSON_UNQUOTE(data->"$.TextBody")');

            $table->timestamps();
        });
    }
}
