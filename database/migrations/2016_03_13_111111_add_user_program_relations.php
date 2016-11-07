<?php

use App\CLH\Repositories\UserRepository;
use App\Practice;
use App\Role;
use App\User;
use Illuminate\Database\Migrations\Migration;
use Symfony\Component\HttpFoundation\ParameterBag;

class AddUserProgramRelations extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		// get participant role id
		$participantRole = Role::where('name', '=', 'participant')->first();
		if(!$participantRole) {
			dd('no participant role found, required');
		}
		$participantRoleId = $participantRole->id;
		echo PHP_EOL.' participant role found id = '.$participantRoleId;

		// remove all participant user_program relations
		$participantRole->users()->sync([]);
		echo PHP_EOL.' removed all participant role attachments';

		// get programs
        $programs = Practice::all();
        //$program = Practice::where('id','=','22')->first();
		//$programs = array($program);
		$i = 0;
		foreach($programs as $program) {
            echo PHP_EOL . 'Processing program:: ' . $program->id . ' (' . $program->display_name . ')';
			/*
			$programUsers = User::where('program_id', '=', $program->id)
				->whereHas('roles', function ($q) {
					$q->where('name', '=', 'participant');
				})
				->get(); */
			$programUsers = User::whereHas('meta', function ($q) use ($program) {
                $q->where('meta_key', '=', 'wp_' . $program->id . '_capabilities');
					$q->where('meta_value', 'LIKE', '%participant%');
				})
				->get();
			if($programUsers->count() > 1) {
				echo PHP_EOL.'total = '. $programUsers->count();
				//continue 1;
				foreach($programUsers as $programUser) {
                    echo PHP_EOL . $i . '-' . $programUser->email . ' linked to program ' . $program->id;
                    $programUser->program_id = $program->id;
					$programUser->save();
					$bag = new ParameterBag([
                        'program_id' => $program->id,
					]);
					$userRepo = new UserRepository();
					$userRepo->saveOrUpdatePrograms($programUser, $bag);
					$bag = new ParameterBag([
						'role' => $participantRoleId,
					]);
					$userRepo->saveOrUpdateRoles($programUser, $bag);
					$i++;
				}
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
