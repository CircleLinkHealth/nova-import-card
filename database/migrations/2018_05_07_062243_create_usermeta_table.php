<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUsermetaTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('usermeta');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('usermeta', function (Blueprint $table) {
            $table->bigInteger('umeta_id', true)->unsigned();
            $table->bigInteger('user_id')->unsigned()->default(0)->index('user_id');
            $table->string('meta_key')->nullable()->index('usermeta_meta_key');
            $table->text('meta_value')->nullable();
        });
    }
}
