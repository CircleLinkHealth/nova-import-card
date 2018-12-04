<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyFieldsToNullableInEnrolleesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('enrollees', function (Blueprint $table) {
            $table->string('other_phone')->nullable()->change();
            $table->string('home_phone')->nullable()->change();
            $table->text('invite_code', 65535)->nullable()->change();
            $table->string('status')->nullable()->change();
            $table->integer('attempt_count')->unsigned()->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('enrollees', function (Blueprint $table) {
            $table->string('other_phone')->nullable(false)->change();
            $table->string('home_phone')->nullable(false)->change();
            $table->text('invite_code', 65535)->nullable(false)->change();
            $table->string('status')->nullable(false)->change();
            $table->integer('attempt_count')->unsigned()->nullable(false)->change();
        });
    }
}
