<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use Illuminate\Database\Migrations\Migration;

class SetFriendlyNamesOnChargeableServicesTable extends Migration
{
    const FRIENDLY_NAMES = [
        'CPT 99490'          => 'CCM',
        'CPT 99439(>40mins)' => 'CCM40',
        'CPT 99439(>60mins)' => 'CCM60',
        'CPT 99484'          => 'BHI',
        'G0511'              => 'CCM (RHC/FQHC)',
        'AWV: G0438'         => 'AWV1',
        'AWV: G0439'         => 'AWV2+',
        'G2065'              => 'PCM',
        'CPT 99457'          => 'RPM',
        'CPT 99458'          => 'RPM40',
    ];

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('chargeable_services')
            ->whereIn('code', ['CPT 99457', 'CPT 99458'])
            ->delete();
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('chargeable_services')
            ->insert([
                'order'        => 10,
                'code'         => 'CPT 99457',
                'display_name' => self::FRIENDLY_NAMES['CPT 99457'],
                'description'  => 'Remote Patient Monitoring',
            ]);

        DB::table('chargeable_services')
            ->insert([
                'order'        => 11,
                'code'         => 'CPT 99458',
                'display_name' => self::FRIENDLY_NAMES['CPT 99458'],
                'description'  => 'Remote Patient Monitoring over 40 minutes',
            ]);

        foreach (self::FRIENDLY_NAMES as $code => $friendlyName) {
            DB::table('chargeable_services')
                ->where('code', '=', $code)
                ->update([
                    'display_name' => $friendlyName,
                ]);
        }
    }
}
