<?php

use App\Models\CPM\CpmMisc;
use Illuminate\Database\Migrations\Migration;

class RenameOtherConditions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        CpmMisc::whereName('Other Conditions')
            ->update([
                'name' => 'Full Conditions List',
            ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
