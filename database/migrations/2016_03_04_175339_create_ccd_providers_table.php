<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCcdProvidersTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create( 'ccd_provider_logs', function (Blueprint $table) {
            $table->increments( 'id' );

            $table->unsignedInteger( 'ccda_id' );
            $table->foreign( 'ccda_id' )
                ->references( 'id' )
                ->on( 'ccdas' )
                ->onUpdate( 'cascade' )
                ->onDelete( 'cascade' );

            $table->unsignedInteger( 'vendor_id' );
            $table->foreign( 'vendor_id' )
                ->references( 'id' )
                ->on( 'ccd_vendors' )
                ->onUpdate( 'cascade' )
                ->onDelete( 'cascade' );

            $table->string( 'npi' )->nullable()->default( null );
            $table->string( 'first_name' )->nullable()->default( null );
            $table->string( 'last_name' )->nullable()->default( null );

            $table->string( 'street' )->nullable()->default( null );
            $table->string( 'city' )->nullable()->default( null );
            $table->string( 'state' )->nullable()->default( null );
            $table->string( 'zip', 5 )->nullable()->default( null );

            $table->string( 'cell_phone', 12 )->nullable()->default( null );
            $table->string( 'home_phone', 12 )->nullable()->default( null );
            $table->string( 'work_phone', 12 )->nullable()->default( null );
            $table->softDeletes();
            $table->timestamps();
        } );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop( 'ccd_provider_logs' );
    }

}
