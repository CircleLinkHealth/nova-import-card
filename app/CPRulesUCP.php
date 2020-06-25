<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

/**
 * App\CPRulesUCP.
 *
 * @property int                                      $ucp_id
 * @property int|null                                 $items_id
 * @property int|null                                 $user_id
 * @property string|null                              $meta_key
 * @property string|null                              $meta_value
 * @property \App\CPRulesItem|null                    $item
 * @property \CircleLinkHealth\Customer\Entities\User $user
 * @method   static                                   \Illuminate\Database\Eloquent\Builder|\App\CPRulesUCP whereItemsId($value)
 * @method   static                                   \Illuminate\Database\Eloquent\Builder|\App\CPRulesUCP whereMetaKey($value)
 * @method   static                                   \Illuminate\Database\Eloquent\Builder|\App\CPRulesUCP whereMetaValue($value)
 * @method   static                                   \Illuminate\Database\Eloquent\Builder|\App\CPRulesUCP whereUcpId($value)
 * @method   static                                   \Illuminate\Database\Eloquent\Builder|\App\CPRulesUCP whereUserId($value)
 * @mixin \Eloquent
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection $revisionHistory
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\CPRulesUCP newModelQuery()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\CPRulesUCP newQuery()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\App\CPRulesUCP query()
 * @property int|null                                                                                    $revision_history_count
 */
class CPRulesUCP extends \CircleLinkHealth\Core\Entities\BaseModel
{
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['ucp_id', 'items_id', 'user_id', 'meta_key', 'meta_value'];

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'ucp_id';

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'rules_ucp';

    public function getCPRulesUCP($userId)
    {
        return CPRulesUCP::where('user_id', '=', $userId)->get();
    }

    public function getCPRulesUCPDetails($userId)
    {
        $rulesUCP = CPRulesUCP::where('user_id', '=', $userId)->get();

        foreach ($rulesUCP as $rules) {
            $rules['item'] = $rules->item;
        }

        return $rulesUCP;
    }

    public function item()
    {
        return $this->belongsTo(\App\CPRulesItem::class, 'items_id');
    }

    public function user()
    {
        return $this->hasOne('CircleLinkHealth\Customer\Entities\User', 'user_id');
    }
}
