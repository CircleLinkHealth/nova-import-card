<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePermissiblesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::dropIfExists('lv_permission_role');

        Schema::create('permissibles', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('permission_id');
            $table->unsignedInteger('permissible_id');
            $table->string('permissible_type');
            $table->boolean('is_active')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('permissibles');

    }
}
