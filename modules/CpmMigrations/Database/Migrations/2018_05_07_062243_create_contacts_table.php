<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContactsTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('contacts');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create(
            'contacts',
            function (Blueprint $table) {
                $table->integer('user_id')->unsigned()->index('contacts_user_id_foreign');
                $table->string('name');
                $table->integer('contactable_id')->unsigned();
                $table->string('contactable_type');
                $table->timestamps();
                $table->softDeletes();
            }
        );
    }
}
