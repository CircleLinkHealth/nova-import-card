<?php

use App\FaxLog;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EditFaxLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $tableName = (new FaxLog())->getTable();
        if ( ! Schema::hasColumn($tableName, 'event_type')) {
            Schema::table(
                $tableName,
                function (Blueprint $table) {
                    $table->string('event_type')->nullable()->after('status');
                }
            );
            
            FaxLog::orderBy('id')->chunkById(30, function ($faxes){
                foreach ($faxes as $fax) {
                    $fax->event_type = $fax->status;
                    $fax->status = $fax->response['status'];
                    $fax->save();
                }
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
        //
    }
}
