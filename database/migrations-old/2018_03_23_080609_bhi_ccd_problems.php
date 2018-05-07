<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class BhiCcdProblems extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('cpm_problems', 'is_behavioral')) {
            Schema::table('cpm_problems', function (Blueprint $table) {
                $table->boolean('is_behavioral')->default(false)->after('contains');
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
        if (Schema::hasColumn('cpm_problems', 'is_behavioral')) {
            Schema::table('cpm_problems', function (Blueprint $table) {
                $table->dropColumn('is_behavioral');
            });
        }
    }
}
