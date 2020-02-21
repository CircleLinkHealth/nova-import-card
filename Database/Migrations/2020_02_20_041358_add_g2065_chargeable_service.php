<?php

use Illuminate\Database\Migrations\Migration;

class AddG2065ChargeableService extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $now = \Carbon\Carbon::now();
        DB::table('chargeable_services')->updateOrInsert([
            'code' => 'G2065',
        ], [
            'order'       => 9,
            'is_enabled'  => true,
            'description' => 'PCM: Principal Care Management over 30 Minutes (1 month)',
            'amount'      => null,
            'created_at'  => $now,
            'updated_at'  => $now,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('chargeable_services')->where('code', '=', 'G2065')->delete();
    }
}
