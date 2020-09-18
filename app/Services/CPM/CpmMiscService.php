<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services\CPM;

use CircleLinkHealth\SharedModels\Contracts\CpmModel;
use App\Repositories\CpmMiscRepository;
use App\Repositories\CpmMiscUserRepository;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Services\CpmInstructionService;

class CpmMiscService implements CpmModel
{
    private $cpmMiscRepo;
    private $cpmMiscUserRepo;

    public function __construct(CpmMiscRepository $cpmMiscRepo, CpmMiscUserRepository $cpmMiscUserRepo)
    {
        $this->cpmMiscRepo     = $cpmMiscRepo;
        $this->cpmMiscUserRepo = $cpmMiscUserRepo;
    }

    public function addMiscToPatient($miscId, $userId)
    {
        if ($this->repo()->exists($miscId)) {
            return $this->cpmMiscUserRepo->addMiscToPatient($miscId, $userId);
        }
        throw new \Exception('misc with id "'.$miscId.'" does not exist');
    }

    public function editPatientMisc($userId, $miscId, $instructionId)
    {
        return $this->cpmMiscUserRepo->editPatientMisc($userId, $miscId, $instructionId);
    }

    public function getMiscWithInstructionsForUser(User $user, $miscName)
    {
        $user->loadMissing('cpmMiscUserPivot.cpmInstruction', 'cpmMiscUserPivot.cpmMisc');
        $misc = $user->cpmMiscUserPivot->where('cpmMisc.name', $miscName)->first();
        //For the CPM Misc Item, extract the instruction and
        //store in a key value pair
        if ($misc) {
            $instruction = $misc->cpmInstruction;
        } else {
            return '';
        }

        if ($instruction) {
            return $instruction->name;
        }

        return '';
    }

    public function miscPatients($miscId)
    {
        return $this->cpmMiscUserRepo->miscPatients($miscId);
    }

    public function patientMisc($userId)
    {
        return $this->cpmMiscUserRepo->patientMisc($userId);
    }

    public function patientMiscByType($userId, $miscTypeId)
    {
        return $this->cpmMiscUserRepo->patientMisc($userId, $miscTypeId)->first();
    }

    public function removeInstructionFromPatientMisc($userId, $miscId, $instructionId)
    {
        return $this->cpmMiscUserRepo->removeInstructionFromPatientMisc($userId, $miscId, $instructionId);
    }

    public function removeMiscFromPatient($miscId, $userId)
    {
        return $this->cpmMiscUserRepo->removeMiscFromPatient($miscId, $userId);
    }

    public function repo()
    {
        return $this->cpmMiscRepo;
    }

    public function syncWithUser(User $user, array $ids, $page, array $instructions)
    {
        if ( ! is_int($page)) {
            throw new \Exception('The page number needs to be an integer.');
        }

        //get careplan templateMiscs id
        $templateMiscs = $user->service()
            ->firstOrDefaultCarePlan($user)
            ->carePlanTemplate()
            ->first()
            ->cpmMiscs()
            ->wherePage($page)
            ->get();

        //get cpmMiscs on cptMiscsIds with this page
        $cptMiscsIds = $templateMiscs
            ->pluck('id')
            ->all();

        //get the user's miscs
        $userMiscs = $user->cpmMiscs->pluck('id')->all();

        //If ids is an empty array, then detach all cptMiscsIds miscs and return
        if (empty($ids)) {
            foreach ($cptMiscsIds as $cptMiscId) {
                $user->cpmMiscs()->detach($cptMiscId);
            }

            return true;
        }

        $instructionService = app(CpmInstructionService::class);

        //otherwise attach/detach each one
        foreach ($cptMiscsIds as $cptMiscId) {
            //check if $cptMiscId needs to be attached or detached
            //
            //IF A $cptMiscId IS NOT CONTAINED IN $ids THEN IT WILL BE DETACHED
            //ie. just like Laravel's sync()

            //if it's in $ids keep it, or detach it
            if (in_array($cptMiscId, $ids)) {
                if ( ! in_array($cptMiscId, $userMiscs)) {
                    //if the field is not already related attach it
                    $user->cpmMiscs()->attach($cptMiscId);
                }

                $relationship  = 'cpmMiscs';
                $entityId      = $cptMiscId;
                $entityForeign = 'cpm_misc_id';

                if (isset($instructions[$relationship][$entityId])) {
                    $instructionInput = $instructions[$relationship][$entityId];

                    $instructionService->syncWithUser($user, $relationship, $entityForeign, $entityId, $instructionInput);
                }
            } else {
                $user->cpmMiscs()->detach($cptMiscId);
            }
        }

        return true;
    }
}
