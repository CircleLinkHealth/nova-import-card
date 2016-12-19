<?php

use App\Models\Ehr;
use App\Services\PdfReports\Dispatchers\AthenaApiPdfDispatcher;
use App\Services\PdfReports\Dispatchers\QueueForPickupPdfDispatcher;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEhrsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ehrs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('pdf_dispatcher');
            $table->timestamps();
        });

        Ehr::create([
            'name'           => 'Aprima',
            'pdf_dispatcher' => QueueForPickupPdfDispatcher::class,
        ]);

        Ehr::create([
            'name'           => 'Athena',
            'pdf_dispatcher' => AthenaApiPdfDispatcher::class,
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ehrs');
    }
}
