<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyChargeablesUniqueKey extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('chargeables', function (Blueprint $table) {

            $keyExists = DB::select(
                DB::raw(
                    'SHOW KEYS
                    FROM chargeables
                    WHERE Key_name=\'chargeables_chargeable_service_id_chargeable_id_unique\''
                )
            );

            if ($keyExists) {
                $table->dropUnique('chargeables_chargeable_service_id_chargeable_id_unique');
            }

            $table->unique(['chargeable_service_id','chargeable_id', 'chargeable_type'], 'cs_id_c_id_ct_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('chargeables', function (Blueprint $table) {
            $table->dropUnique(['chargeable_service_id', 'chargeable_id', 'chargeable_type']);
        });
    }
}
