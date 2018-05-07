<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddImportedDocumentQaFields extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('imported_medical_records', function (Blueprint $table) {
            $table->unsignedInteger('duplicate_id')
                ->after('practice_id')
                ->nullable();

            $table->foreign('duplicate_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('imported_medical_records', function (Blueprint $table) {
            $table->dropColumn('duplicate_id');
        });
    }
}
