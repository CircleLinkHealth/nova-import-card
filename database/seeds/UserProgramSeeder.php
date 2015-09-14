<?php

use App\WpUser;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class UserProgramSeeder extends Seeder {

    public function run()
    {
        $users = WpUser::all();
        if($users) {
            foreach($users as $user) {
                $blogId = $user->meta()->where('meta_key', '=', 'primary_blog')->first();
                if($blogId) {
                    $user->program_id = $blogId->meta_value;
                    $user->user_status = 1;
                    $user->save();
                }
            }
        }
    }

}