<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterImportsTables extends Migration
{
    protected $tables = [
        'allergy_imports',
        'demographics_imports',
        'medication_imports',
        'problem_imports'
    ];

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        foreach ($this->tables as $t) {
            DB::statement('set foreign_key_checks = 0');

            try {
                Schema::table($t, function (Blueprint $table) {
                    $table->unsignedInteger('ccda_id')->index()->change();
                });
            } catch (\Exception $e) {
                echo $e->getMessage() . PHP_EOL;


                try {
                    Schema::table($t, function (Blueprint $table) {
                        $table->foreign('ccda_id')
                            ->references('id')
                            ->on('ccdas')
                            ->onUpdate('cascade')
                            ->onDelete('cascade');
                    });
                } catch (\Exception $e) {
                    echo $e->getMessage() . PHP_EOL;
                }

                DB::statement('set foreign_key_checks = 1');
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public
    function down()
    {
        foreach ($this->tables as $t) {

            DB::statement('set foreign_key_checks = 0');

            try {
                Schema::table($t, function (Blueprint $table) {
                    $table->unsignedInteger('ccda_id')->change();
                    $table->dropIndex(['ccda_id']);
                });

                Schema::table($t, function (Blueprint $table) {
                    $table->dropForeign(['ccda_id']);
                });
            } catch (\Exception $e) {
                echo $e->getMessage() . PHP_EOL;
            }

            DB::statement('set foreign_key_checks = 1');
        }
    }
}
