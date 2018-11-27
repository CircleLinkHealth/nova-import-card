<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAuthyId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('country_code')->nullable()->after('id');
            $table->string('phone_number')->nullable()->after('id');
            $table->string('authy_status')->default('unverified')->after('id');
            $table->string('authy_method')->nullable()->after('id');
            $table->boolean('is_authy_enabled')->nullable()->after('id');
            $table->string('authy_id')->nullable()->after('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('authy_method');
            $table->dropColumn('is_authy_enabled');
            $table->dropColumn('country_code');
            $table->dropColumn('phone_number');
            $table->dropColumn('authy_status');
            $table->dropColumn('authy_id');
        });
    }
}
