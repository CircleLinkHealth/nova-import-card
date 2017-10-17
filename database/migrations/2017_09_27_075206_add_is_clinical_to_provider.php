<?php

use App\ProviderInfo;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIsClinicalToProvider extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('provider_info', function (Blueprint $table) {
            $table->boolean('is_clinical')
                ->nullable()
                ->after('id')
                ->default(null);
        });

        foreach (ProviderInfo::withTrashed()->get() as $provider) {
            if ($provider->qualification == 'clinical') {
                $provider->is_clinical = true;
                $provider->qualification = null;
            } elseif ($provider->qualification == 'non-clinical') {
                $provider->is_clinical = false;
                $provider->qualification = null;
            } else {
                $provider->is_clinical = null;
            }

            $provider->save();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('provider_info', function (Blueprint $table) {
            $table->dropColumn('is_clinical');
        });
    }
}
