<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 5/3/16
 * Time: 2:20 PM
 */

namespace App\Services\CPM;


use App\CarePlanTemplate;
use App\Contracts\Services\CpmModel;
use App\Models\CPM\CpmInstruction;
use App\Services\UserService;
use App\User;

class CpmMiscService implements CpmModel
{
    public function syncWithUser(User $user, array $ids, $page, array $instructions)
    {
        if (!is_int($page)) throw new \Exception('The page number needs to be an integer.');

        //get careplan templateMiscs id
        $cptId = $user->service()
            ->firstOrDefaultCarePlan($user)
            ->getCarePlanTemplateIdAttribute();
        
        $templateMiscs = CarePlanTemplate::find($cptId)
            ->cpmMiscs()
            ->wherePage($page)
            ->get();

        //get cpmMiscs on cptMiscsIds with this page
        $cptMiscsIds = $templateMiscs
            ->lists('cpm_misc_id')
            ->all();

        //get the user's miscs
        $userMiscs = $user->cpmMiscs()->getRelatedIds()->all();

        //If ids is an empty array, then detach all cptMiscsIds miscs and return
        if (empty($ids)) {
            foreach ($cptMiscsIds as $cptMiscId) {
                $user->cpmMiscs()->detach($cptMiscId);
            }
            return true;
        }

        //otherwise attach/detach each one
        foreach ($cptMiscsIds as $cptMiscId) {
            //check if $cptMiscId needs to be attached or detached
            //
            //IF A $cptMiscId IS NOT CONTAINED IN $ids THEN IT WILL BE DETACHED
            //ie. just like Laravel's sync()

            //if it's in $ids keep it, or detach it
            if (in_array($cptMiscId, $ids)) {
                if (!in_array($cptMiscId, $userMiscs)) {
                    //if the field is not already related attach it
                    $user->cpmMiscs()->attach($cptMiscId);
                }

//                $relationship = 'cpmMiscs';
//                $entityId = $cptMiscId;
//                $entityForeign = 'cpm_misc_id';
            } else
            {
                $user->cpmMiscs()->detach($cptMiscId);
            }
        }
        return true;
    }

}