<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use App\Services\Observations\ObservationConstants;
use CircleLinkHealth\Core\Entities\BaseModel;
use CircleLinkHealth\Customer\Entities\User;

/**
 * App\Observation.
 *
 * @property int                                                                                         $id
 * @property string                                                                                      $obs_date
 * @property string                                                                                      $obs_date_gmt
 * @property int                                                                                         $comment_id
 * @property int                                                                                         $sequence_id
 * @property string                                                                                      $obs_message_id
 * @property int                                                                                         $user_id
 * @property string                                                                                      $obs_method
 * @property string                                                                                      $obs_key
 * @property string                                                                                      $obs_value
 * @property string                                                                                      $obs_unit
 * @property int                                                                                         $program_id
 * @property int                                                                                         $legacy_obs_id
 * @property \Illuminate\Support\Carbon                                                                  $created_at
 * @property \Illuminate\Support\Carbon                                                                  $updated_at
 * @property \App\Comment                                                                                $comment
 * @property mixed                                                                                       $alert_level
 * @property mixed                                                                                       $alert_log
 * @property mixed                                                                                       $alert_sort_weight
 * @property mixed                                                                                       $alert_status_change
 * @property mixed                                                                                       $alert_status_history
 * @property mixed                                                                                       $starting_observation
 * @property mixed                                                                                       $timezone
 * @property \App\ObservationMeta[]|\Illuminate\Database\Eloquent\Collection                             $meta
 * @property \App\CPRulesQuestions                                                                       $question
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection $revisionHistory
 * @property \CircleLinkHealth\Customer\Entities\User                                                    $user
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\Observation newModelQuery()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\Observation newQuery()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\Observation query()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\Observation whereCommentId($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\Observation whereCreatedAt($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\Observation whereId($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\Observation whereLegacyObsId($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\Observation whereObsDate($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\Observation whereObsDateGmt($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\Observation whereObsKey($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\Observation whereObsMessageId($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\Observation whereObsMethod($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\Observation whereObsUnit($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\Observation whereObsValue($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\Observation whereProgramId($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\Observation whereSequenceId($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\Observation whereUpdatedAt($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\Observation whereUserId($value)
 * @mixin \Eloquent
 * @property int|null $meta_count
 * @property int|null $revision_history_count
 */
class Observation extends BaseModel
{
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'obs_date',
        'obs_date_gmt',
        'comment_id',
        'sequence_id',
        'obs_message_id',
        'user_id',
        'obs_method',
        'obs_key',
        'obs_value',
        'obs_unit',
        'program_id',
        'legacy_obs_id',
        'severity',
    ];
    protected $table = 'lv_observations';

    public function comment()
    {
        return $this->belongsTo(\App\Comment::class);
    }

    public function getAlertLevel()
    {
        if ( ! $this->obs_value) {
            return;
        }
        $value = preg_split('/\\/|\\_/', $this->obs_value)[0];

        if (ObservationConstants::BLOOD_PRESSURE == $this->obs_key) {
            if ($value = (int) $value < 80 || $value >= 180) {
                return 'danger';
            }
            if (($value >= 80 && $value < 100) || ($value >= 130 && $value < 180)) {
                return 'warning';
            }

            return 'success';
        }
        if (ObservationConstants::BLOOD_SUGAR == $this->obs_key) {
            if ($value = (int) $value < 60 || $value >= 350) {
                return 'danger';
            }
            if (($value >= 60 && $value < 80) || ($value >= 140 && $value < 350)) {
                return 'warning';
            }

            return 'success';
        }
        if (ObservationConstants::A1C == $this->obs_key) {
            if ($value = (float) $value > $diabetesLevel = 7.1) {
                return 'danger';
            }

            if ($value >= $normalLevel = 5.7 && $value <= $diabetesLevel) {
                return 'warning';
            }

            return 'success';
        }
        if (ObservationConstants::CIGARETTE_COUNT == $this->obs_key) {
            if ($value = (int) $value < 4) {
                return 'success';
            }

            return 'danger';
        }
        if (ObservationConstants::MEDICATIONS_ADHERENCE_OBSERVATION_TYPE == $this->obs_key) {
            if ('Y' === $value = (string) strtoupper($value)) {
                return 'success';
            }

            if ('N' === $value) {
                return 'danger';
            }

            return;
        }

        return $this->severity;
    }

    public function getAlertLogAttribute()
    {
        $name = '';
        $meta = $this->meta->where('meta_key', '=', 'dm_log')->first();
        if (isset($meta)) {
            $name = $meta->meta_value;
        }

        return $name;
    }

    public function getAlertSortWeightAttribute()
    {
        $name = '';
        $meta = $this->meta->where('meta_key', '=', 'alert_sort_weight')->first();
        if (isset($meta)) {
            $name = $meta->meta_value;
        }

        return $name;
    }

    public function getAlertStatusChangeAttribute()
    {
        $name = '';
        $meta = $this->meta->where('meta_key', '=', 'alert_status_change')->first();
        if (isset($meta)) {
            $name = $meta->meta_value;
        }

        return $name;
    }

    public function getAlertStatusHistoryAttribute()
    {
        $name = '';
        $meta = $this->meta->where('meta_key', '=', 'alert_status_hist')->first();
        if (isset($meta)) {
            $name = $meta->meta_value;
        }

        return $name;
    }

    public function getObservation($obs_id)
    {
        return Observation::where('obs_id', '=', $obs_id)->get();
    }

    public function getObservationsForUser($user_id)
    {
        $observations = Observation::where('user_id', '=', $user_id)->get();

        foreach ($observations as $observation) {
            $observation['meta'] = $observation->meta;
        }

        return $observations;
    }

    public static function getStartingObservation(
        $userId,
        $message_id
    ) {
        /*
        $starting = Observation::whereHas('meta', function($q) use ($message_id)
        {
            $q->where('meta_key', 'starting_observation')
              ->where('message_id', $message_id);

        })->where('user_id', $userId)->pluck('obs_value')->all();
        */

        $starting = Observation::where('user_id', $userId)
            ->whereHas('meta', function ($q) use (
                                   $message_id
                               ) {
                $q->where('meta_key', 'starting_observation')
                    ->where('message_id', $message_id);
            })->first();

        if ($starting) {
            return $starting->obs_value;
        }
        $x = Observation::where('user_id', '=', $userId)->where('obs_message_id', '=', $message_id)->first();

        return isset($x->obs_value)
                ? $x->obs_value
                : 'N/A';
    }

    public function getStartingObservationAttribute()
    {
        $name = 'no';
        $meta = $this->meta->where('meta_key', '=', 'starting_observation')->first();
        if (isset($meta)) {
            $name = $meta->meta_value;
        }

        return $name;
    }

    public function getTimezoneAttribute()
    {
        $name = '';
        $meta = $this->meta->where('meta_key', '=', 'timezone')->first();
        if (isset($meta)) {
            $name = $meta->meta_value;
        }

        return $name;
    }

    public function meta()
    {
        return $this->hasMany(ObservationMeta::class, 'obs_id', 'id');
    }

    public function question()
    {
        return $this->belongsTo(CPRulesQuestions::class, 'obs_message_id', 'msg_id');
    }

    public function user()
    {
        return $this->belongsTo('CircleLinkHealth\Customer\Entities\User', 'user_id', 'id');
    }
}
