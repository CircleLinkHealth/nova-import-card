<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova;

use App\Call;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Boolean;
use Laravel\Nova\Fields\ID;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Fields\Textarea;
use Laravel\Nova\Http\Requests\NovaRequest;

class CpmCallAlert extends Resource
{
    /**
     * The model the resource corresponds to.
     *
     * @var string
     */
    public static $model = \App\CpmCallAlert::class;

    /**
     * The columns that should be searched.
     *
     * @var array
     */
    public static $search = [
        'id',
    ];

    /**
     * The single value that should be used to represent the resource when being displayed.
     *
     * @var string
     */
    public static $title = 'id';

    /**
     * Get the actions available for the resource.
     *
     * @return array
     */
    public function actions(Request $request)
    {
        return [];
    }

    public static function authorizedToCreate(Request $request)
    {
        return false;
    }

    public function authorizedToDelete(Request $request)
    {
        return false;
    }

    public function authorizedToUpdate(Request $request)
    {
        return true;
    }

    /**
     * Get the cards available for the request.
     *
     * @return array
     */
    public function cards(Request $request)
    {
        return [];
    }

    /**
     * Get the fields displayed by the resource.
     *
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            ID::make(__('ID'), 'id')->sortable(),

            Text::make('Note', function ($row) {
                if ( ! $row->cpmCall->note_id) {
                    return '-';
                }
                $url = route('patient.note.view', [
                    'patientId' => $row->cpmCall->inbound_cpm_id,
                    'noteId'    => $row->cpmCall->note_id,
                ]);

                return "<a href='$url' target='_blank'>View Note</a>";
            })->asHtml(),

            Boolean::make('Resolved', 'resolved'),

            Textarea::make('Call(s)', function ($row) {
                $voiceCalls = $row->cpmCall->voiceCalls;
                if ( ! $voiceCalls) {
                    return '-';
                }

                $msgs = [];
                foreach ($voiceCalls as $voiceCall) {
                    $voiceCallable = $voiceCall->voiceCallable;
                    if (is_a($voiceCallable, \App\TwilioCall::class)) {
                        $duration = $voiceCallable->dial_conference_duration;
                        $status = $voiceCallable->dial_call_status ?? $voiceCallable->call_status;
                        $createdAt = $voiceCallable->created_at->toDateTimeString();
                        $msgs[] = "At: $createdAt - Call Status: $status - Duration: $duration";
                    }
                }

                return implode("\n", $msgs);
            }),

            Text::make('Comment', 'comment')
                ->hideFromIndex(true)
                ->rules('required'),

            Text::make('Info', function ($row) {
                return Call::REACHED === $row->status ? 'Flagged because call was marked as successful but call is below minimum duration'
                    : 'Flagged because call was marked as unsuccessful but call is above minimum duration';
            }),
        ];
    }

    /**
     * Get the filters available for the resource.
     *
     * @return array
     */
    public function filters(Request $request)
    {
        return [];
    }

    public static function indexQuery(NovaRequest $request, $query)
    {
        return $query->with([
            'cpmCall' => function ($q) {
                $q->with([
                    'voiceCalls' => function ($q) {
                        $q->with('voiceCallable');
                    },
                ]);
            },
        ])
            ->where('resolved', '=', 0);
    }

    /**
     * Get the lenses available for the resource.
     *
     * @return array
     */
    public function lenses(Request $request)
    {
        return [];
    }
}
