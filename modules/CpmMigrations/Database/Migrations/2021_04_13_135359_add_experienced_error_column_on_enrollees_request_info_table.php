<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddExperiencedErrorColumnOnEnrolleesRequestInfoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('enrollees_request_info', function (Blueprint $table) {
            $table->boolean('experienced_error')->default(0)->after('enrollable_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('enrollees_request_info', function (Blueprint $table) {
            $table->dropColumn('experienced_error');
        });
    }
}
