<?php

use App\Models\CCD\Problem;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsMonitored extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('ccd_problems', function (Blueprint $table) {
            $table->boolean('is_monitored')
                ->comment('A monitored problem is a problem we provide Care Management for.')
                ->after('id')
                ->default(false);
        });

        Problem::whereNotNull('cpm_problem_id')
            ->orWhere('billable', '=', true)
            ->update([
                'is_monitored' => true
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ccd_problems', function (Blueprint $table) {
            $table->dropColumn('is_monitored');
        });
    }
}
