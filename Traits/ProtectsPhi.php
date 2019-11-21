<?php

namespace CircleLinkHealth\Core\Traits;


use Carbon\Carbon;

trait ProtectsPhi
{
    private $hiddenValue = '***';

    private $hiddenDate = '9999-01-01';

    /**
     *The trait’s boot method works just like an Eloquent model’s boot method.
     * So you can hook in to any of the Eloquent events from here.
     * The boot method of each associated trait will get called at the same time as the model’s boot method
     *
     */
    public static function bootProtectsPhi()
    {
        static::retrieved(function ($model) {
            //this protects phi from getting the model attributes from ->toArray()
            //we could also have overwritten method attributesToArray()
            $model->hidden = array_merge($model->phi, $model->hidden);
        });
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

        $user = auth()->user();

        if ($user) {
            if ($key != 'id' && ! $this->authUserShouldSeePhi() && in_array($key, $this->phi) && ! $user->is($this)) {
                $value = $this->hidePhiAttribute($key);
            }
        }

        return $value;
    }

    private function authUserShouldSeePhi(): bool
    {
        return auth()->user()->canSeePhi();
    }

    private function hidePhiAttribute($key)
    {
        if (in_array($key, $this->casts)) {
            return $this->castHiddenPhiAttribute($key);
        } elseif (in_array($key, $this->dates)){
            return $this->hiddenAttributeCasts()['date'];
        } else {
            return $this->hiddenValue;
        }
    }

    private function castHiddenPhiAttribute($key)
    {

        $castAs = $this->hiddenAttributeCasts();

        return $castAs[$this->casts[$key]];

    }

    private function hiddenAttributeCasts()
    {
        return [
            'date'  => Carbon::parse($this->hiddenDate),
            'array' => [],
        ];
    }

}