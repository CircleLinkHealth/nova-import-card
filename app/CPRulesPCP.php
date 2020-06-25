<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use CircleLinkHealth\Customer\Entities\Practice;

/**
 * App\CPRulesPCP.
 *
 * @property int                                                                                     $pcp_id
 * @property int|null                                                                                $prov_id
 * @property string|null                                                                             $section_text
 * @property string|null                                                                             $status
 * @property int|null                                                                                $cpset_id
 * @property string|null                                                                             $pcp_type
 * @property \App\CPRulesItem[]|\Illuminate\Database\Eloquent\Collection                             $items
 * @property \CircleLinkHealth\Customer\Entities\Practice[]|\Illuminate\Database\Eloquent\Collection $program
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesPCP whereCpsetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesPCP wherePcpId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesPCP wherePcpType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesPCP whereProvId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesPCP whereSectionText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesPCP whereStatus($value)
 * @mixin \Eloquent
 *
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection $revisionHistory
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesPCP newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesPCP newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesPCP query()
 *
 * @property int|null $items_count
 * @property int|null $program_count
 * @property int|null $revision_history_count
 */
class CPRulesPCP extends \CircleLinkHealth\Core\Entities\BaseModel
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['pcp_id', 'prov_id', 'section_text', 'status', 'cpset_id', 'pcp_type'];

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'pcp_id';

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'rules_pcp';

    public function getCPRulesPCPForProv($provId)
    {
        return CPRulesPCP::where('prov_id', '=', $provId)->get();
    }

    public function items()
    {
        return $this->hasMany(\App\CPRulesItem::class, 'pcp_id');
    }

    public function program()
    {
        return $this->hasMany(Practice::class, 'id', 'prov_id');
    }
}
