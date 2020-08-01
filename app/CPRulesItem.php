<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

/**
 * App\CPRulesItem.
 *
 * @property int                                                             $items_id
 * @property int|null                                                        $pcp_id
 * @property int|null                                                        $items_parent
 * @property int|null                                                        $qid
 * @property string                                                          $care_item_id
 * @property string                                                          $name
 * @property string                                                          $display_name
 * @property string                                                          $description
 * @property string|null                                                     $items_text
 * @property string|null                                                     $deleted_at
 * @property \App\CPRulesItemMeta[]|\Illuminate\Database\Eloquent\Collection $meta
 * @property \App\CPRulesPCP|null                                            $pcp
 * @property \App\CPRulesQuestions|null                                      $question
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesItem whereCareItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesItem whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesItem whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesItem whereDisplayName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesItem whereItemsId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesItem whereItemsParent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesItem whereItemsText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesItem whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesItem wherePcpId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesItem whereQid($value)
 * @mixin \Eloquent
 *
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection $revisionHistory
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesItem query()
 *
 * @property int|null $meta_count
 * @property int|null $revision_history_count
 */
class CPRulesItem extends \CircleLinkHealth\Core\Entities\BaseModel
{
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['items_id', 'pcp_id', 'items_parent', 'qid', 'items_text'];

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'items_id';

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'rules_items';

    public static function boot()
    {
        parent::boot();

        // Automatically delete and item's meta when the item is deleted
        CPRulesItem::deleting(function ($CPRulesItem) {
            $CPRulesItem->meta()->delete();
        });
    }

    public function getRulesItem($itemId)
    {
        return CPRulesUCP::where('items_id', '=', $itemId)->get();
    }

    public function meta()
    {
        return $this->hasMany(\App\CPRulesItemMeta::class, 'items_id');
    }

    public function pcp()
    {
        return $this->belongsTo(\App\CPRulesPCP::class, 'pcp_id');
    }

    public function question()
    {
        return $this->belongsTo(\App\CPRulesQuestions::class, 'qid', 'qid');
    }
}
