<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Traits;

use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\User;

trait ProtectsPhi
{
    private $authUser;

    private $hiddenDate  = '9999-01-01';
    private $hiddenValue = '***';

    private $shouldHidePhi;

    /**
     *The trait’s boot method works just like an Eloquent model’s boot method.
     * So you can hook in to any of the Eloquent events from here.
     * The boot method of each associated trait will get called at the same time as the model’s boot method.
     */
    public static function bootProtectsPhi()
    {
        $user = auth()->user();

        // trait might be used with User classes that do not have the method implemented (Cpm Caller)
        if ($user && method_exists($user, 'canSeePhi')) {
            if ( ! $user->canSeePhi()) {
                static::retrieved(function ($model) {
                    //this protects phi from getting the model attributes from ->toArray()
                    //we could also have overwritten method attributesToArray()
                    $model->hidden = array_merge($model->phi, $model->hidden);
                });
            }
        }
    }

    /**
     * @param $key
     *
     * This protects phi by accessing the model property
     *
     * @return string
     */
    public function getAttribute($key)
    {
        $value = parent::getAttribute($key);

        if ('id' === $key) {
            return $value;
        }

        if ( ! in_array($key, $this->phi)) {
            return $value;
        }

        $isUser = $this instanceof User;
        if ($isUser && ! $this->isParticipant()) {
            return $value;
        }

        if ( ! $this->authUser) {
            $this->authUser = auth()->user();
        }

        if ($this->authUser) {
            if (null === $this->shouldHidePhi) {
                $this->shouldHidePhi = ! $this->authUser->canSeePhi();
            }
        }

        if ($this->shouldHidePhi) {
            if (in_array($key, $this->phi)) {
                if (optional($this->authUser)->is($this)) {
                    return $value;
                }
                $value = $this->hidePhiAttribute($key);
            }
        }

        return $value;
    }

    /**
     * To help test the trait.
     */
    public function setShouldHidePhi(bool $bool)
    {
        if (isUnitTestingEnv()) {
            $this->shouldHidePhi = $bool;
        }
    }

    /**
     * @param $key
     *
     * @return mixed
     */
    private function castHiddenPhiAttribute($key)
    {
        $castAs = $this->hiddenAttributeCasts();

        return $castAs[$this->casts[$key]];
    }

    private function hiddenAttributeCasts(): array
    {
        return [
            'date'  => Carbon::parse($this->hiddenDate),
            'array' => [],
        ];
    }

    /**
     * @param $key
     *
     * @return string
     */
    private function hidePhiAttribute($key)
    {
        if (array_key_exists($key, $this->casts)) {
            return $this->castHiddenPhiAttribute($key);
        }
        if (in_array($key, $this->dates)) {
            return $this->hiddenAttributeCasts()['date'];
        }

        return $this->hiddenValue;
    }
}
