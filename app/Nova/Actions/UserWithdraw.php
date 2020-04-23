<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Actions;

use App\Note;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\User;
use Epartment\NovaDependencyContainer\HasDependencies;
use Epartment\NovaDependencyContainer\NovaDependencyContainer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\Select;
use Laravel\Nova\Fields\Textarea;

class UserWithdraw extends Action implements ShouldQueue
{
    use HasDependencies;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public $name = 'Withdraw';

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        return [
            Select::make('Reason', 'reason')
                ->options([
                    'No Longer Interested'               => 'No Longer Interested',
                    'Moving out of Area'                 => 'Moving out of Area',
                    'New Physician'                      => 'New Physician',
                    'Cost / Co-Pay'                      => 'Cost / Co-Pay',
                    'Changed Insurance'                  => 'Changed Insurance',
                    'Dialysis / End-Stage Renal Disease' => 'Dialysis / End-Stage Renal Disease',
                    'Expired'                            => 'Expired',
                    'Patient in Hospice'                 => 'Patient in Hospice',
                    'Other'                              => 'Other',
                ])
                ->required(),

            NovaDependencyContainer::make([
                Textarea::make('Other Reason', 'reason_other')
                    ->rows(5)
                    ->required(),
            ])->dependsOn('reason', 'Other'),
        ];
    }

    /**
     * Perform the action on the given models.
     *
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        $reason = $fields->get('reason');
        if ('Other' === $reason) {
            $reason = $fields->get('reason_other');
        }

        if (empty($reason)) {
            $models->each(function ($model) {
                $this->markAsFailed($model, 'Need to supply a reason');
            });

            return;
        }

        $patientIds = $models->pluck('id');
        $this->withdrawUsers($patientIds, $reason);

        $models->each(function ($model) {
            $this->markAsFinished($model);
        });
    }

    private function createWithdrawNotes($patientIds, $withdrawReason)
    {
        $authorId = auth()->id();
        $notes    = [];
        foreach ($patientIds->all() as $count => $userId) {
            $notes[] = [
                'patient_id'   => $userId,
                'author_id'    => $authorId,
                'logger_id'    => $authorId,
                'body'         => $withdrawReason,
                'type'         => 'Other',
                'created_at'   => Carbon::now(),
                'updated_at'   => Carbon::now(),
                'performed_at' => Carbon::now(),
            ];
        }

        Note::insert($notes);
    }

    private function withdrawUsers($userIds, string $withdrawnReason)
    {
        //need to make sure that we are creating notes for participants
        //and withdrawn patients that are not already withdrawn
        $participantIds = User::ofType('participant')
            ->select('id')
            ->withCount(['inboundCalls'])
            ->whereHas(
                'patientInfo',
                function ($query) {
                    $query->whereNotIn(
                        'ccm_status',
                        [Patient::WITHDRAWN, Patient::WITHDRAWN_1ST_CALL]
                    );
                }
            )
            ->whereIn('id', $userIds)
            ->pluck('id', 'inbound_calls_count');

        //See which patients are on first call to update statuses accordingly
        [$withdrawn1stCall, $withdrawn] = $participantIds->partition(
            function ($value, $key) {
                return $key <= 1;
            }
        );

        Patient::whereIn('user_id', $withdrawn)
            ->update(
                [
                    'ccm_status'       => Patient::WITHDRAWN,
                    'withdrawn_reason' => $withdrawnReason,
                    'date_withdrawn'   => Carbon::now()->toDateTimeString(),
                ]
            );

        Patient::whereIn('user_id', $withdrawn1stCall)
            ->update(
                [
                    'ccm_status'       => Patient::WITHDRAWN_1ST_CALL,
                    'withdrawn_reason' => $withdrawnReason,
                    'date_withdrawn'   => Carbon::now()->toDateTimeString(),
                ]
            );

        $this->createWithdrawNotes($participantIds, $withdrawnReason);
    }
}
