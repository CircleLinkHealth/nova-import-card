<?php namespace App\CLH\Repositories;

use App\CarePlan;
use App\CarePlanTemplate;
use App\NurseInfo;
use App\Patient;
use App\PhoneNumber;
use App\Practice;
use App\ProviderInfo;
use App\Role;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\ParameterBag;

class UserRepository implements \App\CLH\Contracts\Repositories\UserRepository
{

    public function createNewUser(
        User $user,
        ParameterBag $params
    ) {
        $user = $user->createNewUser($params->get('email'), $params->get('password'));

        // set registration date field on users
        $user->user_registered = date('Y-m-d H:i:s');

        // the basics
        $this->saveOrUpdateUserInfo($user, $params);

        // roles
        $this->saveOrUpdateRoles($user, $params);

        // programs
        $this->saveOrUpdatePrograms($user, $params);

        // phone numbers
        $this->saveOrUpdatePhoneNumbers($user, $params);

        // participant info
        if ($user->hasRole('participant')) {
            $this->saveOrUpdatePatientInfo($user, $params);
        }

        // provider info
        if ($user->hasRole('provider')) {
            $this->saveOrUpdateProviderInfo($user, $params);
        }

        // nurse info
        if ($user->hasRole('care-center')) {
            $this->saveOrUpdateNurseInfo($user, $params);
        }

        //Add Email Notification
        $sendTo = ['patientsupport@circlelinkhealth.com'];
        if (app()->environment('production')) {
            $this->adminEmailNotify($user, $sendTo);
        }
        $user->push();

        return $user;
    }

    public function saveOrUpdateUserInfo(
        User $user,
        ParameterBag $params
    ) {
        $user->username = $params->get('username');
        $user->user_status = $params->get('user_status');

        if ($params->get('email')) {
            $user->email = $params->get('email');
        }

        if ($params->get('access_disabled')) {
            $user->access_disabled = $params->get('access_disabled');
        } else {
            $user->access_disabled = 0; // 0 = good, 1 = disabled
        }
        $user->program_id = $params->get('program_id');

        $user->attachPractice($params->get('program_id'));

        $user->auto_attach_programs = $params->get('auto_attach_programs');
        if ($params->get('first_name')) {
            $user->first_name = $params->get('first_name');
        }
        if ($params->get('last_name')) {
            $user->last_name = $params->get('last_name');
        }
        if ($params->get('address')) {
            $user->address = $params->get('address');
        }
        if ($params->get('address2')) {
            $user->address2 = $params->get('address2');
        }
        if ($params->get('city')) {
            $user->city = $params->get('city');
        }
        if ($params->get('state')) {
            $user->state = $params->get('state');
        }
        if ($params->get('zip')) {
            $user->zip = $params->get('zip');
        }
        if ($params->get('timezone')) {
            $user->timezone = $params->get('timezone');
        }
        $user->save();
    }

    public function saveOrUpdateRoles(
        User $user,
        ParameterBag $params
    ) {
        // support for both single or array or roles
        if (!empty($params->get('role'))) {
            $user->roles()->sync([$params->get('role')]);
            $user->save();
            $user->load('roles');
        }

        if (!empty($params->get('roles'))) {
            // support if one role is passed in as a string
            if (!is_array($params->get('roles'))) {
                $roleId = $params->get('roles');
                $user->roles()->sync([$roleId]);
            } else {
                $user->roles()->sync($params->get('roles'));
            }
        }

        // add patient info
        if ($user->hasRole('participant') && !$user->patientInfo) {
            $patientInfo = new Patient;
            $patientInfo->user_id = $user->id;
            $patientInfo->save();
            $user->load('patientInfo');
        }

        // add provider info
        if ($user->hasRole('provider') && !$user->providerInfo) {
            $providerInfo = new ProviderInfo;
            $providerInfo->user_id = $user->id;
            $providerInfo->save();
            $user->load('providerInfo');
        }

        // add nurse info
        if ($user->hasRole('care-center') && !$user->nurseInfo) {
            $nurseInfo = new NurseInfo;
            $nurseInfo->user_id = $user->id;
            $nurseInfo->save();
            $user->load('nurseInfo');
        }
    }

    public function saveOrUpdatePrograms(
        User $wpUser,
        ParameterBag $params
    ) {
        // get selected programs
        $userPrograms = [];
        if ($params->get('programs')) { // && ($wpUser->practices->count() > 0)
            $userPrograms = $params->get('programs');
        }
        if ($params->get('program_id')) {
            if (!in_array($params->get('program_id'), $userPrograms)) {
                $userPrograms[] = $params->get('program_id');
            }
        }

        //dd($userPrograms);

        // if still empty at this point, no program_id or program param
        if (empty($userPrograms)) {
            return true;
        }

        // set primary program
        $wpUser->program_id = $params->get('program_id');
        $wpUser->save();

        // get role
        $roleId = $params->get('role');
        if ($roleId) {
            $role = Role::find($roleId);
        } else {
            // default to participant
            $role = Role::where('name', '=', 'participant')->first();
        }

        // first detatch relationship
        $wpUser->practices()->detach();

        $wpBlogs = Practice::orderBy('id', 'desc')->pluck('id')->all();
        foreach ($wpBlogs as $wpBlogId) {
            if (in_array($wpBlogId, $userPrograms)) {
                $wpUser->practices()->attach($wpBlogId);
            }
        }
    }

    public function saveOrUpdatePhoneNumbers(
        User $user,
        ParameterBag $params
    ) {
        // phone numbers
        if (!empty($params->get('study_phone_number'))) { // add study as home
            $phoneNumber = $user->phoneNumbers()->where('type', 'home')->first();
            if (!$phoneNumber) {
                $phoneNumber = new PhoneNumber;
            }
            $phoneNumber->is_primary = 1;
            $phoneNumber->user_id = $user->id;
            $phoneNumber->number = $params->get('study_phone_number');
            $phoneNumber->type = 'home';
            $phoneNumber->save();
        }
        if (!empty($params->get('home_phone_number'))) {
            $phoneNumber = $user->phoneNumbers()->where('type', 'home')->first();
            if (!$phoneNumber) {
                $phoneNumber = new PhoneNumber;
            }
            $phoneNumber->is_primary = 1;
            $phoneNumber->user_id = $user->id;
            $phoneNumber->number = $params->get('home_phone_number');
            $phoneNumber->type = 'home';
            $phoneNumber->save();
        }
        if (!empty($params->get('work_phone_number'))) {
            $phoneNumber = $user->phoneNumbers()->where('type', 'work')->first();
            if (!$phoneNumber) {
                $phoneNumber = new PhoneNumber;
            }
            $phoneNumber->user_id = $user->id;
            $phoneNumber->number = $params->get('work_phone_number');
            $phoneNumber->type = 'work';
            $phoneNumber->save();
        }
        if (!empty($params->get('mobile_phone_number'))) {
            $phoneNumber = $user->phoneNumbers()->where('type', 'mobile')->first();
            if (!$phoneNumber) {
                $phoneNumber = new PhoneNumber;
            }
            $phoneNumber->user_id = $user->id;
            $phoneNumber->number = $params->get('mobile_phone_number');
            $phoneNumber->type = 'mobile';
            $phoneNumber->save();
        }
    }

    public function saveOrUpdatePatientInfo(
        User $user,
        ParameterBag $params
    ) {
        $user->attachLocation($params->get('preferred_contact_location'));

        $patientInfo = $user->patientInfo->toArray();

        // contact days checkbox formatting, @todo this is not normalized properly?
        if (is_array($params->get('contact_days'))) {
            $contactDays = $params->get('contact_days');
            $contactDaysDelmited = '';
            for ($i = 0; $i < count($contactDays); $i++) {
                $contactDaysDelmited .= (count($contactDays) == $i + 1)
                    ? $contactDays[$i]
                    : $contactDays[$i] . ', ';
            }
            $params->add(['preferred_cc_contact_days' => $contactDaysDelmited]);
        }

        if ($params->has('careplan_status')) {
            CarePlan::create([
                'user_id'               => $user->id,
                'care_plan_template_id' => CarePlanTemplate::whereType(CarePlanTemplate::CLH_DEFAULT)->first()->id,
                'status'                => $params->get('careplan_status'),
            ]);

            $params->remove('careplan_status');
        }

        foreach ($patientInfo as $key => $value) {
            // hack for date_paused and date_withdrawn
            if (
                $key == 'date_paused'
                || $key == 'date_withdrawn'
            ) {
                continue 1;
            }
            if ($params->get($key)) {
                $user->patientInfo->$key = $params->get($key);
            }
        }
        $user->patientInfo->save();
    }

    public function saveOrUpdateProviderInfo(
        User $user,
        ParameterBag $params
    ) {
        $providerInfo = $user->providerInfo->toArray();

        foreach ($providerInfo as $key => $value) {
            if ($params->get($key)) {
                $user->providerInfo->$key = $params->get($key);
            }
        }
        $user->providerInfo->save();
    }

    public function saveOrUpdateNurseInfo(
        User $user,
        ParameterBag $params
    ) {
        $nurseInfo = $user->nurseInfo->toArray();

        foreach ($nurseInfo as $key => $value) {
            if ($params->get($key)) {
                $user->nurseInfo->$key = $params->get($key);
            }
        }
        $user->nurseInfo->save();
    }

    public function adminEmailNotify(
        User $user,
        $recipients
    ) {

//   Template:
//        From: CircleLink Health
//        Sent: Tuesday, January 5, 10:11 PM
//        Subject: [Site Name] New User Registration!
//        To: Linda Warshavsky  lindaw@circlelinkhealth.com
//
//New user registration on Dr Daniel A Miller, MD: Username: WHITE, MELDA JEAN [834] E-mail: test@gmail.com

        $email_view = 'emails.newpatientnotify';
        $program = Practice::find($user->primaryProgramId());
        $program_name = $program->display_name;
        $email_subject = '[' . $program_name . '] New User Registration!';
        $data = [
            'patient_name'  => $user->getFullNameAttribute(),
            'patient_id'    => $user->id,
            'patient_email' => $user->getEmailForPasswordReset(),
            'program'       => $program_name,
        ];

        Mail::send($email_view, $data, function ($message) use
        (
            $recipients,
            $email_subject
        ) {
            $message->from('no-reply@careplanmanager.com', 'CircleLink Health');
            $message->to($recipients)->subject($email_subject);
        });

    }

    public function editUser(
        User $user,
        ParameterBag $params
    ) {
        // the basics
        $this->saveOrUpdateUserInfo($user, $params);

        // roles
        $this->saveOrUpdateRoles($user, $params);

        // programs
        $this->saveOrUpdatePrograms($user, $params);

        // phone numbers
        $this->saveOrUpdatePhoneNumbers($user, $params);

        // participant info
        if ($user->hasRole('participant')) {
            $this->saveOrUpdatePatientInfo($user, $params);
        }

        // provider info
        if ($user->hasRole('provider')) {
            $this->saveOrUpdateProviderInfo($user, $params);
        }

        // nurse info
        if ($user->hasRole('care-center')) {
            $this->saveOrUpdateNurseInfo($user, $params);
        }

        return $user;
    }

    public function saveOrUpdateUserMeta(
        User $user,
        ParameterBag $params
    ) {
        /*
        $userMetaTemplate = (new UserMetaTemplate())->getArray();

        foreach($userMetaTemplate as $key => $defaultValue)
        {
            $paramValue = $params->get($key);

            //serialize arrays
            if($paramValue && is_array($paramValue)) {
                $paramValue = serialize($paramValue);
            } else if($defaultValue && is_array($defaultValue)) {
                $defaultValue = serialize($defaultValue);
            }

            // use existing value if form input wasnt passed in (only updating partial data)
            if(!$params->get($key)) {
                $meta = $user->meta->where('meta_key', $key)->first();
                if (!empty($meta)) {
                    $paramValue = $meta->meta_value;
                    $params->add(array($key => $paramValue));
                }
            }


            // set new value
            $newValue = $defaultValue;
            if($params->get($key)) {
                $newValue = $paramValue;
            }

            // since first/last name are now on user model
            if($key == 'first_name' ||
                $key == 'last_name' ||
                $key == 'city' ||
                $key == 'state' ||
                $key == 'address' ||
                $key == 'address2' ||
                $key == 'zip') {
                $user->$key = $newValue;
                $user->save();
                continue 1;
            }

            // the rest of the attributes
            if($params->get($key)) {
                $user->setUserAttributeByKey($key, $newValue);
            }
        }
        */
    }

    public function updateUserConfig(
        User $wpUser,
        ParameterBag $params
    ) {
        /*
        // meta
        $userMeta = UserMeta::where('user_id', '=', $wpUser->id)->pluck('meta_value', 'meta_key')->all();

        // config
        $userConfig = (new UserConfigTemplate())->getArray();
        if (isset($userMeta['wp_' . $wpUser->program_id . '_user_config'])) {
            $userConfig = unserialize($userMeta['wp_' . $wpUser->program_id . '_user_config']);
            $userConfig = array_merge((new UserConfigTemplate())->getArray(), $userConfig);
        }

        // contact days checkbox formatting
        if($params->get('contact_days')) {
            $contactDays = $params->get('contact_days');
            $contactDaysDelmited = '';
            for($i=0; $i < count($contactDays); $i++){
                $contactDaysDelmited .= (count($contactDays) == $i+1) ? $contactDays[$i] : $contactDays[$i] . ', ';
            }
            $params->add(array('preferred_cc_contact_days' => $contactDaysDelmited));
        }

        foreach($userConfig as $key => $value)
        {
            $paramValue = $params->get($key);

            //serialize arrays
            if($paramValue && is_array($paramValue)) {
                $paramValue = serialize($paramValue);
            } else if($value && is_array($value)) {
                $value = serialize($value);
            }

            // set new value
            $newValue = $value;
            if($params->get($key)) {
                $newValue = $paramValue;
            }

            $wpUser->setUserAttributeByKey($key, $newValue);
        }
        */
    }

    public function findByRole(
        $role,
        $select = '*'
    ) {
        return User::select(DB::raw($select))
            ->whereHas('roles', function ($q) use
            (
                $role
            ) {
                $q->where('name', '=', $role);
            })->get();
    }
}
