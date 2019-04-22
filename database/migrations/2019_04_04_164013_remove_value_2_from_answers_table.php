<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveValue2FromAnswersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('answers', function (Blueprint $table) {
            if (Schema::hasColumn('answers', 'value_1')) {
                $table->json('value_1')->change();
                $table->renameColumn('value_1', 'value');
            }

            if (Schema::hasColumn('answers', 'value_2')) {
                $table->dropColumn('value_2');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('answers', function (Blueprint $table) {
            if (Schema::hasColumn('answers', 'value')) {
                $table->renameColumn('value', 'value_1');
                $table->string('value_1')->change();
            }

            if ( ! Schema::hasColumn('answers', 'value_2')) {
                $table->json('value_2');
            }

        });
    }
}
