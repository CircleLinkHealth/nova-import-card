<?php

use App\Role;
use App\User;
use App\WpBlog;
use App\CLH\Repositories\UserRepository;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Symfony\Component\HttpFoundation\ParameterBag;

class RemoveOldPrograms extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		// get programs
		$programs = WpBlog::where('blog_id', '<', '7')->get();
		$i = 0;
		$programIds = array('');
		foreach($programs as $program) {
			$programIds[] = $program->blog_id;
		}
		foreach($programIds as $programId) {
			$program = WpBlog::where('blog_id', '=', $programId)->first();
			if(empty($program)) {
				echo PHP_EOL . 'Processing program:: EMPTY';
			} else {
				echo PHP_EOL . 'Processing program:: ' . $program->blog_id . ' (' . $program->display_name . ')';
			}
			$programUsers = User::whereHas('roles', function ($q) {
				$q->where('name', '=', 'participant');
			});
			if(!empty($program)) {
				$programUsers->whereHas('programs', function ($q) use ($program) {
					$q->where('blog_id', '=', $program->blog_id);
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
					echo PHP_EOL.$i. '-'.$programUser->user_email . '('.$programUser->ID.') deleted';
					$i++;
				}
			}
			// drop tables
			if($program) {
				echo PHP_EOL.'deleted program '. $program->blog_id;
				Schema::dropIfExists('ma_' . $program->blog_id . '_observations');
				Schema::dropIfExists('ma_' . $program->blog_id . '_observationmeta');
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
