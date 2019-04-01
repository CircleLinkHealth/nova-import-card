<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

/**
 * App\CarePlanItem.
 *
 * @property \App\CareItem                                                $careItem
 * @property \App\CarePlan                                                $carePlan
 * @property \App\CareSection                                             $careSection
 * @property \App\CarePlanItem[]|\Illuminate\Database\Eloquent\Collection $children
 * @property \App\CarePlanItem                                            $parents
 * @mixin \Eloquent
 *
 * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlanItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlanItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlanItem query()
 */
class CarePlanItem extends \CircleLinkHealth\Core\Entities\BaseModel
{
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['parent_id', 'name', 'display_name', 'description'];

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'care_item_care_plan';

    public function careItem()
    {
        return $this->belongsTo(\App\CareItem::class, 'item_id', 'id');
    }

    public function carePlan()
    {
        return $this->belongsTo('App\CarePlan', 'plan_id', 'id');
    }

    public function careSection()
    {
        return $this->belongsTo('App\CareSection', 'section_id', 'id');
    }

    public function children()
    {
        return $this->hasMany('App\CarePlanItem', 'parent_id');
    }

    public function parents()
    {
        return $this->belongsTo('App\CarePlanItem', 'parent_id');
    }
}
