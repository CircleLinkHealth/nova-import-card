<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePermissiblesTable extends Migration
{
    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::dropIfExists('permissibles');
    }

    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('permissibles', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('permission_id');
            $table->unsignedInteger('permissible_id');
            $table->string('permissible_type');
            $table->boolean('is_active')->default(1);
            $table->timestamps();

            $table->unique(['permission_id', 'permissible_id', 'permissible_type'], 'p_id_pble_id_pt_id');
        });
    }
}
