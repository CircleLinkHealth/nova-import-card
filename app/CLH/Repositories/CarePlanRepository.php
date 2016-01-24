<?php namespace App\CLH\Repositories;

use App\CareItemCarePlan;
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
        $carePlan->name = $params->get('name');
        $carePlan->display_name = $params->get('display_name');
        $carePlan->type = $params->get('type');
        $carePlan->user_id = $params->get('user_id');
        $carePlan->save();
        return $carePlan;
    }

    public function updateCarePlan(CarePlan $carePlan, ParameterBag $params)
    {
        $carePlan->name = $params->get('name');
        $carePlan->display_name = $params->get('display_name');
        $carePlan->type = $params->get('type');
        $carePlan->user_id = $params->get('user_id');
        $carePlan->save();
        return $carePlan;
    }

    public function duplicateCarePlan(CarePlan $carePlan, ParameterBag $params)
    {
        // create new careplan
        $carePlanDupe = $this->createCarePlan(new CarePlan, $params);
        $carePlanDupe->save();

        // build careplan
        $carePlan->build();

        // copy each item
        foreach($carePlan->careSections as $careSection) {
            // attach if doesnt already exist
            $carePlanSection = $carePlanDupe->careSections()->where('section_id', '=', $careSection['id'])->first();
            if(empty($carePlanSection)) {
                $carePlanDupe->careSections()->attach(array($careSection['id'] => array('status' => 'active')));
            }
            $carePlanItems = CareItemCarePlan::where('plan_id', '=', $carePlan->id)->where('section_id', '=', $careSection['id'])->get();
            foreach ($carePlanItems as $planItem) {
                $rowData = array(
                'section_id' => $careSection->id,
                "meta_key" => $planItem->meta_key,
                "meta_value" => $planItem->meta_value,
                "status" => $planItem->status,
                "alert_key" => $planItem->alert_key,
                "ui_placeholder" => $planItem->ui_placeholder,
                "ui_default" => $planItem->ui_default,
                "ui_title" => $planItem->ui_title,
                "ui_fld_type" => $planItem->ui_fld_type,
                "ui_show_detail" => $planItem->ui_show_detail,
                "ui_row_start" => $planItem->ui_row_start,
                "ui_row_end" => $planItem->ui_row_end,
                "ui_sort" => $planItem->ui_sort,
                "ui_col_start" => $planItem->ui_col_start,
                "ui_col_end" => $planItem->ui_col_end,
                "ui_track_as_observation" => $planItem->ui_track_as_observation,
                "msg_app_en" => $planItem->msg_app_en,
                "msg_app_es" => $planItem->msg_app_es);
                $carePlanDupe->careItems()->attach(array($planItem['item_id'] => $rowData));
            }
        }

        $carePlanDupe->push();

        // now populate parent ids
        $carePlanItems = CareItemCarePlan::where('plan_id', '=', $carePlanDupe->id)->get();
        if($carePlanItems->count() > 0) {
            foreach ($carePlanItems as $carePlanItem) {

                // skip if no care item relation
                if (is_null($carePlanItem->careItem)) {
                    continue 1;
                }
                // skip if care_item.parent_id = 0
                if($carePlanItem->careItem->parent_id == 0) {
                    continue 1;
                }
                // get parent care item
                $careItemParent = CareItem::where('id', '=', $carePlanItem->careItem->parent_id)->first();
                // skip if no care item relation
                if (empty($careItemParent)) {
                    continue 1;
                }
                // get id for parent care plan item
                $carePlanItemParent = CareItemCarePlan::where('item_id', '=', $careItemParent->id)
                    ->where('plan_id', '=', $carePlanDupe->id)
                    ->first();
                // skip if no care item relation
                if (empty($carePlanItemParent)) {
                    continue 1;
                }
                // update
                $carePlanItem->parent_id = $carePlanItemParent->id;
                $carePlanItem->save();
            }
        }

        return $carePlanDupe;
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
