<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIsEnabledAndOrderInChargableServicesTable extends Migration
{
    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('chargeable_services', function (Blueprint $table) {
            if (Schema::hasColumn('chargeable_services', 'order')) {
                $table->dropColumn('order');
            }
            if (Schema::hasColumn('chargeable_services', 'is_enabled')) {
                $table->dropColumn('is_enabled');
            }
        });

        $g2058_1 = DB::table('chargeable_services')
            ->where('code', '=', 'G2058(>40mins)')
            ->first();

        if ($g2058_1) {
            DB::table('chargeable_services')
                ->delete($g2058_1->id);
        }

        $g2058_2 = DB::table('chargeable_services')
            ->where('code', '=', 'G2058(>60mins)')
            ->first();

        if ($g2058_2) {
            DB::table('chargeable_services')
                ->delete($g2058_2->id);
        }
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $dbTable = 'chargeable_services';
        Schema::table($dbTable, function (Blueprint $table) {
            $table->integer('order')
                ->unique()
                ->nullable(true)
                ->default(null)
                ->after('id');

            $table->boolean('is_enabled')
                ->default(true)
                ->after('amount');
        });

        DB::table($dbTable)
            ->insert([
                'order'       => 3,
                'code'        => 'G2058(>40mins)',
                'description' => 'CCM services over 40 mins (1 month)',
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

        DB::table($dbTable)
            ->insert([
                'order'       => 4,
                'code'        => 'G2058(>60mins)',
                'description' => 'CCM services over 60 mins (1 month)',
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

        DB::table($dbTable)
            ->where('code', '=', 'CPT 99487')
            ->orWhere('code', '=', 'CPT 99489')
            ->orWhere('code', '=', 'G0506')
            ->update([
                'is_enabled' => false,
            ]);

        $this->setOrder('CPT 99490', 1);
        $this->setOrder('CPT 99484', 2);
        $this->setOrder('G0511', 5);
        $this->setOrder('Software-Only', 6);
        $this->setOrder('AWV: G0438', 7);
        $this->setOrder('AWV: G0439', 8);
    }

    private function setOrder(string $code, int $order)
    {
        DB::table('chargeable_services')
            ->where('code', '=', $code)
            ->update([
                'order' => $order,
            ]);
    }
}
