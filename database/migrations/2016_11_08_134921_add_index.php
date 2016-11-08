<?php

use App\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddIndex extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $allRelations = DB::select('select * from practice_user');

        foreach ($allRelations as $rel) {
            $user = User::find($rel->user_id);

            if (!$user) {
                DB::delete('delete from practice_user where user_id = ?', [$rel->user_id]);
            }
        }

        Schema::table('practice_user', function (Blueprint $table) {

            $table->dropPrimary('PRIMARY');

            $table->unsignedInteger('user_id')
                ->change();

            Schema::disableForeignKeyConstraints();

            //we are not cascading on delete to account for the soft deletes on the user model
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade');

            Schema::enableForeignKeyConstraints();

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('practice_user', function (Blueprint $table) {
            //
        });
    }
}
