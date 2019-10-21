<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddShortUrlInInvitationLinksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('invitation_links', function (Blueprint $table) {
            $table->string('url')->nullable(true);
            $table->string('short_url')->nullable(true);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('invitation_links', function (Blueprint $table) {
            $table->dropColumn(['url', 'short_url']);
        });
    }
}
