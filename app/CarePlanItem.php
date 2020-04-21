<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

/**
 * App\CarePlanItem.
 *
 * @property \App\CareItem                                                $careItem
 * @property \CircleLinkHealth\SharedModels\Entities\CarePlan             $carePlan
 * @property \App\CareSection                                             $careSection
 * @property \App\CarePlanItem[]|\Illuminate\Database\Eloquent\Collection $children
 * @property \App\CarePlanItem                                            $parents
 * @mixin \Eloquent
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection $revisionHistory
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\CarePlanItem newModelQuery()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\CarePlanItem newQuery()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\CarePlanItem query()
 * @property int|null                                                                                    $children_count
 * @property int|null                                                                                    $revision_history_count
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
        return $this->belongsTo('CircleLinkHealth\SharedModels\Entities\CarePlan', 'plan_id', 'id');
    }

    public function careSection()
    {
        return $this->belongsTo(\App\CareSection::class, 'section_id', 'id');
    }

    public function children()
    {
        return $this->hasMany(\App\CarePlanItem::class, 'parent_id');
    }

    public function parents()
    {
        return $this->belongsTo(\App\CarePlanItem::class, 'parent_id');
    }
}
