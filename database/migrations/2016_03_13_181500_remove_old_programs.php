<?php

use App\Practice;
use App\User;
use Illuminate\Database\Migrations\Migration;

class RemoveOldPrograms extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		// get programs
        $programs = Practice::where('id', '<', '7')->get();
		$i = 0;
		$programIds = array('');
		foreach($programs as $program) {
            $programIds[] = $program->id;
		}
		foreach($programIds as $programId) {
            $program = Practice::where('id', '=', $programId)->first();
			if(empty($program)) {
				echo PHP_EOL . 'Processing program:: EMPTY';
			} else {
                echo PHP_EOL . 'Processing program:: ' . $program->id . ' (' . $program->display_name . ')';
			}
			$programUsers = User::whereHas('roles', function ($q) {
				$q->where('name', '=', 'participant');
			});
			if(!empty($program)) {
                $programUsers->whereHas('practices', function ($q) use
                (
                    $program
                ) {
                    $q->where('id', '=', $program->id);
				});
			} else {
				$programUsers->where('program_id', '=', '');
			}
			$programUsers = $programUsers->get();
			if($programUsers->count() > 1) {
				echo PHP_EOL.'total = '. $programUsers->count();
				//continue 1;
				foreach($programUsers as $programUser) {
					$programUser->delete();
                    echo PHP_EOL . $i . '-' . $programUser->email . '(' . $programUser->id . ') deleted';
					$i++;
				}
			}
			// drop tables
			if($program) {
                echo PHP_EOL . 'deleted program ' . $program->id;
                Schema::dropIfExists('ma_' . $program->id . '_observations');
                Schema::dropIfExists('ma_' . $program->id . '_observationmeta');
				$program->delete();
			}
		}
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		// cant reverse this
	}

}
