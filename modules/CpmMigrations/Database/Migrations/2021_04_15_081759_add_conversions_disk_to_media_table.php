<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class AddConversionsDiskToMediaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasColumn('media', 'uuid')){
            Schema::table('media', function (Blueprint $table) {
                $table->string('uuid','36')
                    ->nullable()
                    ->default(Str::uuid());
            });
        }

        if (! Schema::hasColumn('media', 'conversions_disk')){
            Schema::table('media', function (Blueprint $table) {
                $table->string('conversions_disk', '255')
                    ->nullable()
                    ->default('disk');
            });
        }

        if (! Schema::hasColumn('media', 'generated_conversions')){
            Schema::table('media', function (Blueprint $table) {
                $table->json('generated_conversions');
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
            Schema::table('media', function (Blueprint $table) {
                $table->dropColumn('uuid');
                $table->dropColumn('conversions_disk');
                $table->dropColumn('generated_conversions');
            });
    }
}
