<?php

namespace CircleLinkHealth\Core\Traits;


trait ProtectsPhi
{
    /**
     *The trait’s boot method works just like an Eloquent model’s boot method.
     * So you can hook in to any of the Eloquent events from here.
     * The boot method of each associated trait will get called at the same time as the model’s boot method
     *
     */
    public static function bootProtectsPhi(){
        static::retrieved(function ($model){
            //this protects phi from getting the model attributes from ->toArray()
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

        //check if model is patient?
        //check if model is same as auth-> user
        if (in_array($key, $this->phi) && ! $this->authUserShouldSeePhi()) {
            //check in casts or dates, asterisk breaks carbon/dateTime parsing
            if (in_array($key, $this->casts)){
                if ($this->casts[$key] == 'date'){
                    $value = '';
                }
            }
            $value = '*';
        }

        return $value;
    }

    private function authUserShouldSeePhi(){
        //auth user can see phi
        return false;
    }

}