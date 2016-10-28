<?php

use App\CarePlanCareSection;
use App\PhoneNumber;
use App\User;
use Illuminate\Database\Seeder;

class S20160606UserPhoneHome2Migration extends Seeder {


    public function run()
    {
        //$this->outputMetaPhoneNumbers();
        $this->migrateMetaPhoneNumber();
    }

    public function migrateMetaPhoneNumber()
    {
        // first delete all phone2 records
        //$phone2s = PhoneNumber::orderBy('ID', 'desc')->limit(10)->get();
        //dd($phone2s);
        $phone2s = PhoneNumber::where('type', '=', 'home2')->delete();
        //dd($phone2s);
        echo "Deleted all home2 entries, " . $phone2s . " Removed Total" . PHP_EOL;

        $users = User::withTrashed()->with('meta')->get();
        echo 'Process all role users demographics - Users found: '.$users->count().PHP_EOL;
        $u = 1;
        foreach($users as $user) {
            //echo '#'.$u.' Processing user '.$user->ID.PHP_EOL;
            //echo 'Add users home phone'.PHP_EOL;

            // phone numbers
            $studyPhoneNumber = $user->getUserConfigByKey('study_phone_number');
            $homePhoneNumber = $user->getUserConfigByKey('home_phone_number');
            $workPhoneNumber = $user->getUserConfigByKey('work_phone_number');
            $mobilePhoneNumber = $user->getUserConfigByKey('mobile_phone_number');
            if(
                ($homePhoneNumber != '') &&
                ($homePhoneNumber != $studyPhoneNumber)
            ) {
                if($user->ccmStatus != 'enrolled' ) {
                    //continue 1;
                }
                echo PHP_EOL."#".$u." FOUND!! " . $user->ID . PHP_EOL;
                echo PHP_EOL."ccm status: " . $user->ccmStatus . PHP_EOL;
                echo PHP_EOL."careplan status: " . $user->careplanStatus . PHP_EOL;
                echo 'study: '.$studyPhoneNumber . PHP_EOL;
                echo 'home: '.$homePhoneNumber . PHP_EOL;
                echo 'work: '.$workPhoneNumber . PHP_EOL;
                echo 'mobile: '.$mobilePhoneNumber . PHP_EOL;

                // DO WORK, add home 2
                $phoneNumber = new PhoneNumber;
                $phoneNumber->is_primary = 0;
                $phoneNumber->user_id = $user->ID;
                $phoneNumber->number = $user->getUserConfigByKey('home_phone_number');
                $phoneNumber->type = 'home2';
                $phoneNumber->save();
                $u++;
                echo 'Added home2 home_phone_number' . PHP_EOL;
                echo 'Saved ' . PHP_EOL . PHP_EOL;
            }

        }
        dd('fin');
    }

    public function outputMetaPhoneNumbers()
    {
        // seed data user demographics
        //$users = User::withTrashed()->with('meta')->where('ID', '<', '2065')->get();
        $users = User::withTrashed()->with('meta')->get();
        echo 'Process all role users demographics - Users found: '.$users->count().PHP_EOL;
        $u = 1;
        foreach($users as $user) {
            // phone numbers
            $studyPhoneNumber = $user->getUserConfigByKey('study_phone_number');
            $homePhoneNumber = $user->getUserConfigByKey('home_phone_number');
            $workPhoneNumber = $user->getUserConfigByKey('work_phone_number');
            $mobilePhoneNumber = $user->getUserConfigByKey('mobile_phone_number');
            if(
                ($homePhoneNumber != '') &&
                ($homePhoneNumber != $studyPhoneNumber)
            ) {
                if($user->ccmStatus != 'enrolled' ) {
                    continue 1;
                }
                echo PHP_EOL."#".$u." FOUND!! " . $user->ID . PHP_EOL;
                echo PHP_EOL."ccm status: " . $user->ccmStatus . PHP_EOL;
                echo PHP_EOL."careplan status: " . $user->careplanStatus . PHP_EOL;
                echo 'study: '.$studyPhoneNumber . PHP_EOL;
                echo 'home: '.$homePhoneNumber . PHP_EOL;
                echo 'work: '.$workPhoneNumber . PHP_EOL;
                echo 'mobile: '.$mobilePhoneNumber . PHP_EOL;
                $u++;
            }
        }
        dd('fin');
    }
}