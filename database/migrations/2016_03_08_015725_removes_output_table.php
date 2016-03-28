<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemovesOutputTable extends Migration
{

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table( 'q_a_import_summaries', function (Blueprint $table) {
            if ( Schema::hasColumn( 'q_a_import_summaries', 'qa_output_id' ) ) {
                $table->dropForeign('q_a_import_summaries_qa_output_id_foreign');
                $table->dropColumn( 'qa_output_id' );
            }

            if ( !Schema::hasColumn( 'q_a_import_summaries', 'ccda_id' ) ) {
                $table->unsignedInteger( 'ccda_id' )->after( 'id' )->nullable();
                $table->foreign( 'ccda_id' )
                    ->references( 'id' )
                    ->on( 'ccdas' )
                    ->onUpdate( 'cascade' )
                    ->onDelete( 'cascade' );
            }

            if ( !Schema::hasColumn( 'q_a_import_summaries', 'flag' ) ) {
                $table->boolean( 'flag' )->after( 'ccda_id' );
            }
        } );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table( 'q_a_import_summaries', function (Blueprint $table) {
            $table->dropForeign( 'ccdas_ccda_id_foreign' );
            $table->dropColumn( 'ccda_id' );
        } );
    }

}
