<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToPracticesTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('practices', function (Blueprint $table) {
            $table->foreign('ehr_id')->references('id')->on('ehrs')->onUpdate('CASCADE')->onDelete('SET NULL');
            $table->foreign('saas_account_id')->references('id')->on('saas_accounts')->onUpdate('CASCADE')->onDelete('RESTRICT');
            $table->foreign('user_id', 'wp_blogs_user_id_foreign')->references('id')->on('users')->onUpdate('CASCADE')->onDelete('SET NULL');
        });
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('practices', function (Blueprint $table) {
            $table->dropForeign('practices_ehr_id_foreign');
            $table->dropForeign('practices_saas_account_id_foreign');
            $table->dropForeign('wp_blogs_user_id_foreign');
        });
    }
}
