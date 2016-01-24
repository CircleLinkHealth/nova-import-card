<?php namespace App\CLH\Repositories;

use App\CLH\DataTemplates\UserConfigTemplate;
use App\CLH\DataTemplates\UserMetaTemplate;
use App\CarePlan;
use App\CareItem;
use App\CareSection;
use App\User;
use App\UserMeta;
use App\WpBlog;
use App\Role;
use App\Services\CareplanUIService;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\ParameterBag;

class CarePlanRepository {

    public function createCarePlan(CarePlan $carePlan, ParameterBag $params)
    {
        $carePlan = new CarePlan;
        $carePlan->name = $params->get('name');
        $carePlan->display_name = $params->get('display_name');
        $carePlan->type = $params->get('type');
        $carePlan->user_id = $params->get('user_id');
        $carePlan->save();
        return $carePlan;
    }

    public function updateCarePlan(CarePlan $carePlan, ParameterBag $params)
    {
        $carePlan = new CarePlan;
        $carePlan->name = $params->get('name');
        $carePlan->display_name = $params->get('display_name');
        $carePlan->type = $params->get('type');
        $carePlan->user_id = $params->get('user_id');
        $carePlan->save();
        return $carePlan;
    }

    public function adminEmailNotify(User $user, $recipients){

//   Template:
//        From: CircleLink Health
//        Sent: Tuesday, January 5, 10:11 PM
//        Subject: [Site Name] New User Registration!
//        To: Phil Lawlor       Plawlor@circlelinkhealth.com
//            Linda Warshavsky  lindaw@circlelinkhealth.com
//
//New user registration on Dr Daniel A Miller, MD: Username: WHITE, MELDA JEAN [834] E-mail: test@gmail.com

        $email_view = 'emails.newpatientnotify';
        $program = WpBlog::find($user->blogId());
        $program_name = $program->display_name;
        $email_subject = '[' . $program_name . '] New '. ucwords($user->role()) .' Registration!';
        $data = array(
            'patient_name' => $user->getFullNameAttribute(),
            'patient_id' => $user->ID,
            'patient_email' => $user->getEmailForPasswordReset(),
            'program' => $program_name
        );

        Mail::send($email_view, $data, function($message) use ($recipients,$email_subject) {
            $message->from('no-reply@careplanmanager.com', 'CircleLink Health');
            $message->to($recipients)->subject($email_subject);
        });
    }
}
