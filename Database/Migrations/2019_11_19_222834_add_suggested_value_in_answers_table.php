<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSuggestedValueInAnswersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('answers', function (Blueprint $table) {
            $table->json('value')
                  ->nullable(true)
                  ->default(null)
                  ->change();

            $table->json('suggested_value')
                  ->nullable(true)
                  ->default(null)
                  ->after('value');
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
            if (Schema::hasColumn('answers', 'suggested_value')) {
                $table->dropColumn('suggested_value');
            }
        });

    }
}
