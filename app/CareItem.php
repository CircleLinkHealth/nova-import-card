<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

/**
 * App\CareItem.
 *
 * @property int                                                                                         $id
 * @property string|null                                                                                 $model_field_name
 * @property int|null                                                                                    $type_id
 * @property string|null                                                                                 $type
 * @property string                                                                                      $relationship_fn_name
 * @property int                                                                                         $parent_id
 * @property int                                                                                         $qid
 * @property string                                                                                      $obs_key
 * @property string                                                                                      $name
 * @property string                                                                                      $display_name
 * @property string                                                                                      $description
 * @property \Carbon\Carbon                                                                              $created_at
 * @property \Carbon\Carbon                                                                              $updated_at
 * @property \CircleLinkHealth\SharedModels\Entities\CarePlan[]|\Illuminate\Database\Eloquent\Collection $carePlans
 * @property \App\CareItem[]|\Illuminate\Database\Eloquent\Collection                                    $children
 * @property mixed                                                                                       $meta_key
 * @property \App\CareItem                                                                               $parents
 * @property \App\CPRulesQuestions                                                                       $question
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareItem whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareItem whereDisplayName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareItem whereModelFieldName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareItem whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareItem whereObsKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareItem whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareItem whereQid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareItem whereRelationshipFnName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareItem whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareItem whereTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareItem whereUpdatedAt($value)
 * @mixin \Eloquent
 *
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection $revisionHistory
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CareItem query()
 *
 * @property int|null $care_plans_count
 * @property int|null $children_count
 * @property int|null $revision_history_count
 */
class CareItem extends \CircleLinkHealth\Core\Entities\BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'parent_id',
        'name',
        'display_name',
        'description',
    ];

    public function carePlans()
    {
        return $this->belongsToMany('CircleLinkHealth\SharedModels\Entities\CarePlan', 'care_plan_care_item', 'item_id', 'plan_id')->withPivot('id');
    }

    // START ATTRIBUTES

    public function children()
    {
        return $this->hasMany(\App\CareItem::class, 'parent_id');
    }

    // END ATTRIBUTES

    public function getMetaKeyAttribute()
    {
        return $this->pivot->meta_key;
    }

    public function parents()
    {
        return $this->belongsTo(\App\CareItem::class, 'parent_id');
    }

    public function question() // rules prefix because ->items is a protect class var on parent
    {
        return $this->belongsTo(\App\CPRulesQuestions::class, 'qid', 'qid');
    }
}
