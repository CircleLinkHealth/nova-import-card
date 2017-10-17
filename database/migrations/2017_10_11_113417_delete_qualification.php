<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DeleteQualification extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('provider_info', 'qualification')) {
            return;
        }

        Schema::table('provider_info', function (Blueprint $table) {
            $table->dropColumn('qualification');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('provider_info', function (Blueprint $table) {
            //
        });
    }
}
