<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

/**
 * App\CPRulesItemMeta.
 *
 * @property int                   $itemmeta_id
 * @property int|null              $items_id
 * @property string|null           $meta_key
 * @property string|null           $meta_value
 * @property \App\CPRulesItem|null $CPRulesItem
 * @method   static                \Illuminate\Database\Eloquent\Builder|\App\CPRulesItemMeta whereItemmetaId($value)
 * @method   static                \Illuminate\Database\Eloquent\Builder|\App\CPRulesItemMeta whereItemsId($value)
 * @method   static                \Illuminate\Database\Eloquent\Builder|\App\CPRulesItemMeta whereMetaKey($value)
 * @method   static                \Illuminate\Database\Eloquent\Builder|\App\CPRulesItemMeta whereMetaValue($value)
 * @mixin \Eloquent
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection $revisionHistory
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\CPRulesItemMeta newModelQuery()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\CPRulesItemMeta newQuery()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\CPRulesItemMeta query()
 * @property int|null                                                                                    $revision_history_count
 */
class CPRulesItemMeta extends \CircleLinkHealth\Core\Entities\BaseModel
{
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['itemmeta_id', 'items_id', 'meta_key', 'meta_value'];

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'itemmeta_id';

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'rules_itemmeta';

    public function CPRulesItem()
    {
        return $this->belongsTo(\App\CPRulesItem::class, 'items_id');
    }
}
