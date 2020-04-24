<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;
use Illuminate\Support\Collection;

class NurseInfo extends Resource
{
    public static $time1 = 0;
    public static $time2 = 0;
    public static $time3 = 0;

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request
     * @param mixed $request
     *
     * @return array
     */
    public function toArray($request)
    {
        $nurse = parent::toArray($request);
        if (array_key_exists('states', $nurse) && $request->has('compressed')) {
            $nurse['states'] = (new Collection($nurse['states']))->map(function ($s) {
                return $s['code'];
            });
            //$this->loadMissing('user.practices');
            if ($this->user) {
                $start = microtime(true);
                $dummy = $this->user->isParticipant();
                self::$time1 += microtime(true) - $start;

                $start = microtime(true);
                $bla   = $this->user->allPracticeIds->pluck('id');
                self::$time2 += microtime(true) - $start;

                $start              = microtime(true);
                $nurse['practices'] = $bla;
                self::$time3 += microtime(true) - $start;
            }
        }

        return $nurse;
    }
}
