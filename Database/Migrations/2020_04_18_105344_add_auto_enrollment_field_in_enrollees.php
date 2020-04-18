<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAutoEnrollmentFieldInEnrollees extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('enrollees', 'auto_enrollment_triggered')) {
            Schema::table('enrollees', function (Blueprint $table) {
                $table->boolean('auto_enrollment_triggered')->default(false);
            });

        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('enrollees', function (Blueprint $table) {
            if (Schema::hasColumn('enrollees', 'auto_enrollment_triggered')) {
                $table->dropColumn('auto_enrollment_triggered');
            }
        });
    }
}
