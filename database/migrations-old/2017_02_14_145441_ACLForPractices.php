<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ACLForPractices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('practice_user', function (Blueprint $table) {
            $table->unsignedInteger('role_id')
                ->nullable()
                ->default(null);

            $table->foreign('role_id')
                ->references('id')
                ->on('lv_roles')
                ->onUpdate('cascade')
                ->onDelete('set null');

            $table->boolean('has_admin_rights')
                ->nullable()
                ->default(null);
            $table->boolean('send_billing_reports')
                ->nullable()
                ->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('practice_user', function (Blueprint $table) {
            //
        });
    }
}
