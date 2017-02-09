<?php

use App\Models\Ehr;
use App\Services\PdfReports\Handlers\AprimaApiPdfHandler;
use App\Services\PdfReports\Handlers\AthenaApiPdfHandler;
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
            $table->string('pdf_report_handler');
            $table->timestamps();
        });

        Ehr::create([
            'name'               => 'Aprima',
            'pdf_report_handler' => AprimaApiPdfHandler::class,
        ]);

        Ehr::create([
            'name'               => 'Athena',
            'pdf_report_handler' => AthenaApiPdfHandler::class,
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
