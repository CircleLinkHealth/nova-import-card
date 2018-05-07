<?php

use App\Models\CPM\CpmBiometric;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUnitForBiometricSmoking extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        CpmBiometric::where(['id' => 1])->update(['unit' => 'lbs']);
        CpmBiometric::where(['id' => 2])->update(['unit' => 'mm Hg']);
        CpmBiometric::where(['id' => 3])->update(['unit' => 'mg/dL']);
        CpmBiometric::where(['id' => 4])->update(['unit' => '# per day']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        CpmBiometric::where(['id' => 1])->update(['unit' => null]);
        CpmBiometric::where(['id' => 2])->update(['unit' => null]);
        CpmBiometric::where(['id' => 3])->update(['unit' => null]);
        CpmBiometric::where(['id' => 4])->update(['unit' => null]);
    }
}
