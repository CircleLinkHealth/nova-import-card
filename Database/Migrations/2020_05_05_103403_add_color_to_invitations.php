<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColorToInvitations extends Migration
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
        Schema::table('enrollables_invitation_links', function (Blueprint $table) {
            if ( ! Schema::hasColumn('enrollables_invitation_links', 'button_color')) {
                $table->string('button_color')->nullable();
            }
        });
    }
}
