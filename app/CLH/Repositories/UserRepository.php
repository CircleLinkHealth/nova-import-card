<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\CLH\Repositories;

use App\AuthyUser;
use App\CareAmbassador;
use App\CarePlan;
use App\EhrReportWriterInfo;
use App\Nurse;
use App\Patient;
use App\PatientMonthlySummary;
use App\PhoneNumber;
use App\Practice;
use App\ProviderInfo;
use App\Services\GoogleDrive;
use App\User;
use App\UserPasswordsHistory;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Storage;
use Symfony\Component\HttpFoundation\ParameterBag;

class UserRepository
{
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
        $program    = Practice::find($user->primaryProgramId());

        if ( ! $program) {
            return;
        }

        $program_name  = $program->display_name;
        $email_subject = '['.$program_name.'] New User Registration!';
        $data          = [
            'patient_name'  => $user->getFullName(),
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

    public function createNewUser(
        User $user,
        ParameterBag $params
    ) {
        $user = $user->createNewUser($params->get('email'), $params->get('password'));

        $this->saveOrUpdatePasswordsHistory($user, $params);

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
            $this->saveOrUpdatePatientMonthlySummary($user);
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
        if ($user->hasRole('care-ambassador') || $user->hasRole('care-ambassador-view-only')) {
            $this->saveOrUpdateCareAmbassadorInfo($user, $params);
        }

        // ehr report writer info
        if ($user->hasRole('ehr-report-writer')) {
            $this->saveOrUpdateEhrReportWriterInfo($user, $params);
        }

        if ($user->isAdmin() && $user->authyUser) {
            $this->forceEnable2fa($user->authyUser);
        }

        //Add Email Notification
        $sendTo = ['patientsupport@circlelinkhealth.com'];
        if (app()->environment('production')) {
            $this->adminEmailNotify($user, $sendTo);
        }
        $user->push();

        return $user;
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

        if ($user->hasRole('ehr-report-writer')) {
            $this->saveOrUpdateEhrReportWriterInfo($user, $params);
        }

        return $user;
    }

    public function exists($id)
    {
        return User::where('id', $id)->exists();
    }

    public function saveAndGetPractice(
        User $user,
        ParameterBag $params
    ) {
        // get selected programs
        $userPrograms = [];
        if ($params->get('programs')) {
            $userPrograms = $params->get('programs');
        }

        if ($params->get('program_id')) {
            if ( ! in_array($params->get('program_id'), $userPrograms)) {
                $userPrograms[] = $params->get('program_id');
            }
        }

        // set primary program
        $user->program_id = $params->get('program_id');
        $user->save();

        return array_unique($userPrograms);
    }

    public function saveEhrReportWriterFolder($user)
    {
        $googleDrive = new GoogleDrive();
        $cloudDisk   = Storage::drive('google');

        if (app()->environment(['staging', 'local'])) {
            $ehr = getGoogleDirectoryByName('ehr-data-from-report-writers');

            if ( ! $ehr) {
                $cloudDisk->makeDirectory('ehr-data-from-report-writers');

                return $this->saveEhrReportWriterFolder($user);
            }
        } else {
            $ehrPath = '1NMMNIZKKicOVDNEUjXf6ayAjRbBbFAgh';

            $ehr = $googleDrive->getContents($ehrPath);
        }

        $writerFolder = $ehr->where('type', '=', 'dir')
            ->where('filename', '=', "report-writer-{$user->id}")
            ->first();

        if ( ! $writerFolder) {
            $cloudDisk->makeDirectory($ehrPath."/report-writer-{$user->id}");

            return $this->saveEhrReportWriterFolder($user);
        }
        $service    = $cloudDisk->getAdapter()->getService();
        $permission = new \Google_Service_Drive_Permission();
        $permission->setRole('writer');
        $permission->setType('user');
        $permission->setEmailAddress($user->email);

        $service->permissions->create($writerFolder['basename'], $permission);

        $permission = new \Google_Service_Drive_Permission();
        $permission->setRole('writer');
        $permission->setType('user');
        $permission->setEmailAddress('joe@circlelinkhealth.com');

        $service->permissions->create($writerFolder['basename'], $permission);

        if (app()->environment('staging')) {
            //only staging, so we can have the ability to test, but not get access to PHI
            $devEmails = collect(
                [
                    'constantinos@circlelinkhealth.com',
                    'mAntoniou@circlelinkhealth.com',
                    'antonis@circlelinkhealth.com',
                    'pangratios@circlelinkhealth.com',
                ]
            );

            foreach ($devEmails as $email) {
                $permission = new \Google_Service_Drive_Permission();
                $permission->setRole('writer');
                $permission->setType('user');
                $permission->setEmailAddress($email);
                $service->permissions->create($writerFolder['basename'], $permission);
            }
        }

        return $writerFolder['path'];
    }

    public function saveOrUpdateCareAmbassadorInfo(
        User $user,
        ParameterBag $params
    ) {
        CareAmbassador::updateOrCreate(
            ['user_id' => $user->id],
            [
                'hourly_rate' => $params->get('hourly_rate')
                    ?: null,
                'speaks_spanish' => 'on' == $params->get('speaks_spanish')
                    ? 1
                    : 0,
            ]
        );
    }

    public function saveOrUpdateEhrReportWriterInfo(
        User $user,
        ParameterBag $params
    ) {
        $folderPath = $this->saveEhrReportWriterFolder($user);

        EhrReportWriterInfo::updateOrCreate(
            ['user_id' => $user->id],
            [
                'google_drive_folder_path' => $folderPath,
            ]
        );
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

    /**
     * For now, only used in createUser.
     * Since password is a crucial property, it cannot be updated in
     * editUser method.
     * We could implement a change password page and we could use this method
     * to also populate password history.
     * https://www.5balloons.info/setting-up-change-password-with-laravel-authentication/.
     *
     * @param User         $user
     * @param ParameterBag $params
     */
    public function saveOrUpdatePasswordsHistory(
        User $user,
        ParameterBag $params
    ) {
        $history          = $user->passwordsHistory;
        $previousPassword = $params->get('old-password');
        if ($history) {
            if ($previousPassword) {
                $history->older_password = $history->old_password;
                $history->old_password   = bcrypt($previousPassword);
                $history->save();
            }
        } else {
            $history          = new UserPasswordsHistory();
            $history->user_id = $user->id;
            $history->save();
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
            $contactDays         = $params->get('contact_days');
            $contactDaysDelmited = '';
            for ($i = 0; $i < count($contactDays); ++$i) {
                $contactDaysDelmited .= (count($contactDays) == $i + 1)
                    ? $contactDays[$i]
                    : $contactDays[$i].', ';
            }
            $params->add(['preferred_cc_contact_days' => $contactDaysDelmited]);
        }

        if ($params->has('careplan_status')) {
            CarePlan::updateOrCreate([
                'user_id' => $user->id,
            ], [
                'status' => $params->get('careplan_status'),
                'mode'   => $params->get('careplan_mode', CarePlan::WEB),
            ]);

            $params->remove('careplan_status');
            $params->remove('careplan_mode');
        }

        foreach ($patientInfo as $key => $value) {
            // hack for date_paused and date_withdrawn
            if ('date_paused' == $key
                || 'date_withdrawn' == $key
            ) {
                continue 1;
            }
            if ($params->get($key)) {
                $user->patientInfo->$key = $params->get($key);
            }
        }
        $user->patientInfo->save();
    }

    public function saveOrUpdatePatientMonthlySummary($user)
    {
        return PatientMonthlySummary::updateOrCreate([
            'patient_id' => $user->id,
            'month_year' => Carbon::now()->startOfMonth()->toDateString(),
        ]);
    }

    public function saveOrUpdatePhoneNumbers(
        User $user,
        ParameterBag $params
    ) {
        // phone numbers
        if ($params->has('study_phone_number')) { // add study as home
            $phoneNumber = $user->phoneNumbers()->where('type', 'home')->first();
            if ( ! $phoneNumber) {
                $phoneNumber = new PhoneNumber();
            }
            $phoneNumber->is_primary = 1;
            $phoneNumber->user_id    = $user->id;
            $phoneNumber->number     = $params->get('study_phone_number');
            $phoneNumber->type       = 'home';
            $phoneNumber->save();
        }
        if ($params->has('home_phone_number')) {
            $phoneNumber = $user->phoneNumbers()->where('type', 'home')->first();
            if ( ! $phoneNumber) {
                $phoneNumber = new PhoneNumber();
            }
            $phoneNumber->is_primary = 1;
            $phoneNumber->user_id    = $user->id;
            $phoneNumber->number     = $params->get('home_phone_number');
            $phoneNumber->type       = 'home';
            $phoneNumber->save();
        }
        if ($params->has('work_phone_number')) {
            $phoneNumber = $user->phoneNumbers()->where('type', 'work')->first();
            if ( ! $phoneNumber) {
                $phoneNumber = new PhoneNumber();
            }
            $phoneNumber->user_id = $user->id;
            $phoneNumber->number  = $params->get('work_phone_number');
            $phoneNumber->type    = 'work';
            $phoneNumber->save();
        }

        if ($params->has('mobile_phone_number')) {
            $phoneNumber = $user->phoneNumbers()->where('type', 'mobile')->first();
            if ( ! $phoneNumber) {
                $phoneNumber = new PhoneNumber();
            }
            $phoneNumber->user_id = $user->id;
            $phoneNumber->number  = $params->get('mobile_phone_number');
            $phoneNumber->type    = 'mobile';
            $phoneNumber->save();
        }
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

    public function saveOrUpdateRoles(
        User $user,
        ParameterBag $params
    ) {
        $practices = $this->saveAndGetPractice($user, $params);

        foreach ($practices as $practiceId) {
            if ( ! empty($params->get('role'))) {
                $user->detachRolesForSite([], $practiceId);
                $user->attachRoleForSite($params->get('role'), $practiceId);
            }

            if ( ! empty($params->get('roles'))) {
                $user->detachRolesForSite([], $practiceId);
                // support if one role is passed in as a string
                if ( ! is_array($params->get('roles'))) {
                    $user->attachRoleForSite($params->get('roles'), $practiceId);
                } else {
                    $user->attachRolesForSite($params->get('roles'), $practiceId);
                }
            }
        }

        DB::table('practice_role_user')
            ->where('user_id', $user->id)
            ->whereNotIn('program_id', $practices)
            ->delete();

        // add patient info
        if ($user->hasRole('participant') && ! $user->patientInfo) {
            $patientInfo          = new Patient();
            $patientInfo->user_id = $user->id;
            $patientInfo->save();
            $user->load('patientInfo');
        }

        // add provider info
        if ($user->hasRole('provider') && ! $user->providerInfo) {
            $providerInfo          = new ProviderInfo();
            $providerInfo->user_id = $user->id;
            $providerInfo->save();
            $user->load('providerInfo');
        }

        // add nurse info
        if ($user->hasRole('care-center') && ! $user->nurseInfo) {
            $nurseInfo          = new Nurse();
            $nurseInfo->status  = 'active';
            $nurseInfo->user_id = $user->id;
            $nurseInfo->save();
            $user->load('nurseInfo');
        }

        if ($user->hasRole('ehr-report-writer') && ! $user->ehrReportWriterInfo) {
            $ehrReportWriterInfo          = new EhrReportWriterInfo();
            $ehrReportWriterInfo->user_id = $user->id;
            $ehrReportWriterInfo->save();
            $user->load('ehrReportWriterInfo');
        }
    }

    public function saveOrUpdateUserInfo(
        User $user,
        ParameterBag $params
    ) {
        $user->username    = $params->get('username');
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
            $user->setFirstName($params->get('first_name'));
        }
        if ($params->get('last_name')) {
            $user->setLastName($params->get('last_name'));
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

    private function forceEnable2fa(AuthyUser $authyUser)
    {
        if ($authyUser->authy_id && ! $authyUser->is_authy_enabled) {
            $authyUser->is_authy_enabled = true;

            if ( ! $authyUser->authy_method) {
                $authyUser->authy_method = 'app';
            }

            $authyUser->save();
        }
    }
}
