<?php namespace App\CLH\Repositories;

use App\CareAmbassador;
use App\CarePlan;
use App\Nurse;
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

        // care ambassador info
        if ($user->hasRole('care-ambassador')) {
            $this->saveOrUpdateCareAmbassadorInfo($user, $params);
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

        $user->auto_attach_programs = $params->get('auto_attach_programs');
        if ($params->get('first_name')) {
            $user->first_name = $params->get('first_name');
        }
        if ($params->get('last_name')) {
            $user->last_name = $params->get('last_name');
        }
        if ($params->get('suffix')) {
            $user->suffix = $params->get('suffix');
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
        $practiceId = $this->saveAndGetPractice($user, $params);
        // support for both single or array or roles
        if (!empty($params->get('role'))) {
            $user->detachRolesForSite([], $practiceId);
            $user->attachRoleForSite($params->get('role'), $practiceId);
        }

        if (!empty($params->get('roles'))) {
            $user->detachRolesForSite([], $practiceId);
            // support if one role is passed in as a string
            if (!is_array($params->get('roles'))) {
                $user->attachRoleForSite($params->get('roles'), $practiceId);
            } else {
                $user->attachRolesForSite($params->get('roles'), $practiceId);
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
            $nurseInfo = new Nurse;
            $nurseInfo->user_id = $user->id;
            $nurseInfo->save();
            $user->load('nurseInfo');
        }
    }

    public function saveAndGetPractice(
        User $wpUser,
        ParameterBag $params
    ) {
        // get selected programs
        $userPrograms = [];
        if ($params->get('programs')) {
            $userPrograms = $params->get('programs');
        }
        if ($params->get('program_id')) {
            if (!in_array($params->get('program_id'), $userPrograms)) {
                $userPrograms[] = $params->get('program_id');
            }
        }

        // if still empty at this point, no program_id or program param
        if (empty($userPrograms)) {
            return false;
        }

        // set primary program
        $wpUser->program_id = $params->get('program_id');
        $wpUser->save();

        return $params->get('program_id');
    }

    public function saveOrUpdatePhoneNumbers(
        User $user,
        ParameterBag $params
    ) {
        // phone numbers
        if ($params->has('study_phone_number')) { // add study as home
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
        if ($params->has('home_phone_number')) {
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
        if ($params->has('work_phone_number')) {
            $phoneNumber = $user->phoneNumbers()->where('type', 'work')->first();
            if (!$phoneNumber) {
                $phoneNumber = new PhoneNumber;
            }
            $phoneNumber->user_id = $user->id;
            $phoneNumber->number = $params->get('work_phone_number');
            $phoneNumber->type = 'work';
            $phoneNumber->save();
        }

        if ($params->has('mobile_phone_number')) {
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
            CarePlan::updateOrCreate([
                'user_id' => $user->id,
            ], [
                'status' => $params->get('careplan_status'),
            ]);

            $params->remove('careplan_status');
        }

        foreach ($patientInfo as $key => $value) {
            // hack for date_paused and date_withdrawn
            if ($key == 'date_paused'
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

    public function saveOrUpdateCareAmbassadorInfo(
        User $user,
        ParameterBag $params
    ) {

        if ($user->careAmbassador != null) {
            $user->careAmbassador->hourly_rate = $params->get('hourly_rate');
            $user->careAmbassador->speaks_spanish = $params->get('speaks_spanish') == 'on'
                ? 1
                : 0;
            $user->careAmbassador->save();
        } else {
            $ambassador = CareAmbassador::create([
                'user_id' => $user->id,
            ]);

            $ambassador->save();

            $user->careAmbassador()->save($ambassador);
        }
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

        if (!$program) {
            return;
        }

        $program_name = $program->display_name;
        $email_subject = '[' . $program_name . '] New User Registration!';
        $data = [
            'patient_name'  => $user->getFullNameAttribute(),
            'patient_id'    => $user->id,
            'patient_email' => $user->getEmailForPasswordReset(),
            'program'       => $program_name,
        ];

        Mail::send($email_view, $data, function ($message) use (
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

        // phone numbers
        $this->saveOrUpdatePhoneNumbers($user, $params);

        // participant info
        if ($user->hasRole('participant')) {
            $this->saveOrUpdatePatientInfo($user, $params);
        }

        // care ambassador
        if ($user->hasRole('care-ambassador')) {
            $this->saveOrUpdateCareAmbassadorInfo($user, $params);
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

    public function findByRole(
        $role,
        $select = '*'
    ) {
        return User::select(DB::raw($select))
            ->whereHas('roles', function ($q) use (
                $role
            ) {
                $q->where('name', '=', $role);
            })->get();
    }
}
