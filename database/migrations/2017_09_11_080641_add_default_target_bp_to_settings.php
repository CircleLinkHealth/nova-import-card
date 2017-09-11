<?php

use App\Practice;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDefaultTargetBpToSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('default_target_bp', 7)
                ->default('130/80')
            ->after('efax_audit_reports');
        });

        $lgh = Practice::whereName('lafayette-general-health')->first();

        if (!$lgh) return;

        $settings = $lgh->settings->first();

        if (!$settings) return;

        $settings->default_target_bp = '140/90';
        $settings->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn('default_target_bp');
        });
    }
}
