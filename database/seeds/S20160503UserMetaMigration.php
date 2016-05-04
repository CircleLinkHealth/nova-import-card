<?php

use App\WpBlog;
use App\CarePlan;
use App\CareSection;
use App\CareItem;
use App\CarePlanCareSection;
use App\CarePlanItem;
use App\CPRulesUCP;
use App\CPRulesQuestions;
use App\CPRulesItem;
use App\CPRulesItemMeta;
use App\PatientInfo;
use App\ProviderInfo;
use App\PatientCareTeamMember;
use App\PhoneNumber;
use App\User;
use App\UserMeta;
use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class S20160503UserMetaMigration extends Seeder {


    public function run()
    {
        DB::table('patient_info')->delete();
        DB::table('provider_info')->delete();
        DB::table('patient_care_team_members')->delete();
        DB::table('phone_numbers')->delete();

        $this->migrateUserInfo();
        $this->migratePatientInfo();
        $this->migrateProviderInfo();
    }



    public function migrateUserInfo()
    {
        // seed data user demographics
        $users = User::with('meta')->get();
        echo 'Process all role users demographics - Users found: '.$users->count().PHP_EOL;
        foreach($users as $user) {
            echo 'Processing user '.$user->ID.PHP_EOL;
            echo 'Rebuild User'.PHP_EOL;
            $user->first_name = $user->getUserMetaByKey('first_name');
            $user->last_name = $user->getUserMetaByKey('last_name');
            $user->address = $user->getUserConfigByKey('address');
            $user->address = $user->getUserConfigByKey('address2');
            $user->city = $user->getUserConfigByKey('city');
            $user->state = $user->getUserConfigByKey('state');
            $user->zip = $user->getUserConfigByKey('zip');
            $user->zip = $user->getUserConfigByKey('status');
            $user->save();

            // phone numbers
            if(!empty($user->getUserConfigByKey('study_phone_number'))) {
                $phoneNumber = new PhoneNumber;
                $phoneNumber->is_primary = 1;
                $phoneNumber->user_id = $user->ID;
                $phoneNumber->number = $user->getUserConfigByKey('study_phone_number');
                $phoneNumber->type = 'home';
                $phoneNumber->save();
                echo 'Added home study_phone_number'.PHP_EOL;
            }
            if(!empty($user->getUserConfigByKey('work_phone_number'))) {
                $phoneNumber = new PhoneNumber;
                $phoneNumber->user_id = $user->ID;
                $phoneNumber->number = $user->getUserConfigByKey('work_phone_number');
                $phoneNumber->type = 'work';
                $phoneNumber->save();
                echo 'Added work work_phone_number'.PHP_EOL;
            }
            if(!empty($user->getUserConfigByKey('mobile_phone_number'))) {
                $phoneNumber = new PhoneNumber;
                $phoneNumber->user_id = $user->ID;
                $phoneNumber->number = $user->getUserConfigByKey('mobile_phone_number');
                $phoneNumber->type = 'mobile';
                $phoneNumber->save();
                echo 'Added mobile mobile_phone_number'.PHP_EOL;
            }

            echo 'Saved '.PHP_EOL;
        }
    }


    public function migratePatientInfo()
    {

        // seed data patients
        $users = User::whereHas('roles', function ($q) {
            $q->where('name', '=', 'participant');
        })->with('meta', 'patientInfo')->get();
        echo 'Process role patient users - Users found: '.$users->count().PHP_EOL;
        foreach($users as $user) {
            echo 'Processing user '.$user->ID.PHP_EOL;
            echo 'Rebuild User->PatientInfo'.PHP_EOL;
            // check if has demographics
            //$patientInfo = PatientInfo::where('user_id', $user->ID)->first();

            // delete existing to reprocess
            if($user->patientInfo) {
                echo 'Removing existing patientInfo'.PHP_EOL;
                $user->patientInfo->delete();
            }

            // create new
            echo 'creating new patientInfo'.PHP_EOL;
            $patientInfo = new PatientInfo;
            $patientInfo->user_id = $user->ID;
            $user->patientInfo()->save($patientInfo);
            $user->load('patientInfo');

            // set values
            $user->patientInfo->active_date = $user->getUserConfigByKey('active_date');
            $user->patientInfo->agent_name = $user->getUserConfigByKey('agent_name');
            $user->patientInfo->agent_telephone = $user->getUserConfigByKey('agent_telephone');
            $user->patientInfo->agent_email = $user->getUserConfigByKey('agent_email');
            $user->patientInfo->agent_relationship = $user->getUserConfigByKey('agent_relationship');
            $user->patientInfo->birth_date = $user->getUserConfigByKey('birth_date');
            $user->patientInfo->ccm_status = $user->getUserConfigByKey('ccm_status');
            $user->patientInfo->consent_date = $user->getUserConfigByKey('consent_date');
            $user->patientInfo->cur_month_activity_time = $user->getUserConfigByKey('cur_month_activity_time');
            $user->patientInfo->date_paused = $user->getUserConfigByKey('date_paused');
            $user->patientInfo->date_withdrawn = $user->getUserConfigByKey('date_withdrawn');
            $user->patientInfo->gender = $user->getUserConfigByKey('gender');
            $user->patientInfo->preferred_contact_method = $user->getUserConfigByKey('preferred_contact_method');
            $user->patientInfo->preferred_contact_location = $user->getUserConfigByKey('preferred_contact_location');
            $user->patientInfo->preferred_contact_language = $user->getUserConfigByKey('preferred_contact_language');
            $user->patientInfo->mrn_number = $user->getUserConfigByKey('mrn_number');
            $user->patientInfo->preferred_cc_contact_days = $user->getUserConfigByKey('preferred_cc_contact_days');
            $user->patientInfo->preferred_contact_time = $user->getUserConfigByKey('preferred_contact_time');
            $user->patientInfo->preferred_contact_timezone = $user->getUserConfigByKey('preferred_contact_timezone');
            $user->patientInfo->registration_date = $user->getUserConfigByKey('registration_date');
            $user->patientInfo->daily_reminder_optin = $user->getUserConfigByKey('daily_reminder_optin');
            $user->patientInfo->daily_reminder_time = $user->getUserConfigByKey('daily_reminder_time');
            $user->patientInfo->daily_reminder_areas = $user->getUserConfigByKey('daily_reminder_areas');
            $user->patientInfo->hospital_reminder_optin = $user->getUserConfigByKey('hospital_reminder_optin');
            $user->patientInfo->hospital_reminder_time = $user->getUserConfigByKey('hospital_reminder_time');
            $user->patientInfo->hospital_reminder_areas = $user->getUserConfigByKey('hospital_reminder_areas');

            $user->patientInfo->save();




            // CARE TEAM STUFF
            echo 'CARE TEAM STUFF'.PHP_EOL;
            echo 'Processing user '.$user->ID.PHP_EOL;
            echo 'Deleting existing care team members'.PHP_EOL;
            $user->patientCareTeamMembers()->delete();
            echo 'Migrate Care Team Members from Meta'.PHP_EOL;
            // care team
            $careTeam = $user->getUserConfigByKey('care_team');
            if(!empty($careTeam)) {
                if(is_array($careTeam)) {
                    foreach($careTeam as $ct) {
                        if(is_numeric($ct)) {
                            $careTeamMember = new PatientCareTeamMember;
                            $careTeamMember->user_id = $user->ID;
                            $careTeamMember->member_user_id = $ct;
                            $careTeamMember->type = 'member';
                            $careTeamMember->save();
                            echo 'added member' . PHP_EOL;
                        } else {
                            echo 'member not int = '.$ct.''. PHP_EOL;
                        }
                    }
                } else {
                    if(is_numeric($careTeam)) {
                        $careTeamMember = new PatientCareTeamMember;
                        $careTeamMember->user_id = $user->ID;
                        $careTeamMember->member_user_id = $careTeam;
                        $careTeamMember->type = 'member';
                        $careTeamMember->save();
                        echo 'added member' . PHP_EOL;
                    }
                }
            }

            // care team billing provider
            $careTeamBP = $user->getUserConfigByKey('billing_provider');
            if(!empty($careTeamBP) && is_numeric($careTeamBP)) {
                $careTeamMember = new PatientCareTeamMember;
                $careTeamMember->user_id = $user->ID;
                $careTeamMember->member_user_id = $careTeamBP;
                $careTeamMember->type = 'billing_provider';
                $careTeamMember->save();
                echo 'added billing_provider' . PHP_EOL;
            } else {
                echo 'billing_provider not int = '.$careTeamBP.''. PHP_EOL;
            }

            // care team lead contacts
            $careTeamLC = $user->getUserConfigByKey('lead_contact');
            if(!empty($careTeamLC) && is_numeric($careTeamLC)) {
                $careTeamMember = new PatientCareTeamMember;
                $careTeamMember->user_id = $user->ID;
                $careTeamMember->member_user_id = $careTeamLC;
                $careTeamMember->type = 'lead_contact';
                $careTeamMember->save();
                echo 'added lead_contact'.PHP_EOL;
            } else {
                echo 'lead_contact not int = '.$careTeamLC.''. PHP_EOL;
            }

            // care team send alert to
            $careTeamSA = $user->getUserConfigByKey('send_alert_to');
            if(!empty($careTeamSA)) {
                if(is_array($careTeamSA)) {
                    foreach($careTeamSA as $sa) {
                        if(is_numeric($sa)) {
                            $careTeamMember = new PatientCareTeamMember;
                            $careTeamMember->user_id = $user->ID;
                            $careTeamMember->member_user_id = $sa;
                            $careTeamMember->type = 'send_alert_to';
                            $careTeamMember->save();
                            echo 'added send_alert_to' . PHP_EOL;
                        } else {
                            echo 'send_alert_to not int = '.$sa.''. PHP_EOL;
                        }
                    }
                } else {
                    if(is_numeric($careTeamSA)) {
                        $careTeamMember = new PatientCareTeamMember;
                        $careTeamMember->user_id = $user->ID;
                        $careTeamMember->member_user_id = $careTeamSA;
                        $careTeamMember->type = 'send_alert_to';
                        $careTeamMember->save();
                        echo 'added send_alert_to' . PHP_EOL;
                    }
                }
            }

        }
    }

    public function migrateProviderInfo()
    {
        // seed data provider_info
        $users = User::whereHas('roles', function ($q) {
            $q->where('name', '=', 'provider');
        })->with('meta', 'providerInfo')->get();
        echo 'Process role provider users providerInfo - Users found: '.$users->count().PHP_EOL;
        foreach($users as $user) {
            echo 'Processing user '.$user->ID.PHP_EOL;
            echo 'Rebuild User->ProviderInfo'.PHP_EOL;
            // check if has demographics
            //$providerInfo = ProviderInfo::where('user_id', $user->ID)->first();

            // delete existing to reprocess
            if($user->providerInfo) {
                echo 'Removing existing providerInfo'.PHP_EOL;
                $user->providerInfo->delete();
            }

            // create new
            echo 'creating new providerInfo'.PHP_EOL;
            $providerInfo = new ProviderInfo;
            $providerInfo->user_id = $user->ID;
            $user->providerInfo()->save($providerInfo);
            $user->load('providerInfo');

            // set values
            $user->providerInfo->npi_number = $user->getUserConfigByKey('npi_number');
            $user->providerInfo->prefix = $user->getUserConfigByKey('prefix');
            $user->providerInfo->specialty = $user->getUserConfigByKey('specialty');
            $user->providerInfo->qualification = $user->getUserConfigByKey('qualification');
            $user->providerInfo->save();

            echo PHP_EOL;
        }
    }
}