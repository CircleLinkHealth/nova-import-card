<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateEnrolleesViewTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $viewName = 'enrollees_view';
        \DB::statement("DROP VIEW IF EXISTS ${viewName}");
        \DB::statement("
        CREATE VIEW ${viewName}
        AS
        SELECT e.*, u.display_name AS provider_name, u2.display_name as care_ambassador_name, p.display_name AS practice_name 
        FROM enrollees AS e 
        LEFT JOIN users AS u ON u.id=e.provider_id
        LEFT JOIN users AS u2 ON u2.id=e.care_ambassador_id
        LEFT JOIN practices AS p ON p.id=e.practice_id; 
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $viewName = 'enrollees_view';
        \DB::statement("DROP VIEW IF EXISTS ${viewName}");
    }
}
