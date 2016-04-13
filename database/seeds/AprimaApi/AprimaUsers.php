<?php
use Illuminate\Database\Seeder;

/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 4/1/16
 * Time: 11:50 AM
 */
class AprimaUsers extends Seeder
{
    public function run()
    {
        $upgLocations = \App\Location::whereParentId( 26 )->get();

        $i = 1;

        foreach ( $upgLocations as $loc ) {


            $role = \App\Role::whereName( 'aprima-api-location' )->first();

            if (is_null($role)) {
                throw new \Exception('Role aprima-api-location not found.');
            }

            $password = str_random( 16 );
            $programId = 16;

            $user = \App\User::updateOrCreate( [
                'user_email' => "upg$i@clh.com",
            ], [
                'password' => \Hash::make( $password ),
                'program_id' => $programId,
            ] );

            $relationships = DB::table('location_user')->where('user_id', '=', $user->ID)->where('location_id', '=', $loc->id)->count();
            
            if ($relationships < 1) $user->locations()->attach( $loc->id );

            //attach role if it's not there
            if (! $user->hasRole('aprima-api-location')) $user->attachRole( $role );

            $i++;

            $this->command->info( 'Email: ' . $user->user_email . ' Password: ' . $password . ' Location: ' . $loc->name );
        }

    }
}