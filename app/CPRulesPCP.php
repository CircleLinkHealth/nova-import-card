<?php namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\CPRulesPCP
 *
 * @property int $pcp_id
 * @property int|null $prov_id
 * @property string|null $section_text
 * @property string|null $status
 * @property int|null $cpset_id
 * @property string|null $pcp_type
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\CPRulesItem[] $items
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Practice[] $program
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesPCP whereCpsetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesPCP wherePcpId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesPCP wherePcpType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesPCP whereProvId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesPCP whereSectionText($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesPCP whereStatus($value)
 * @mixin \Eloquent
 */
class CPRulesPCP extends \App\BaseModel
{



    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'rules_pcp';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'pcp_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['pcp_id', 'prov_id', 'section_text', 'status', 'cpset_id', 'pcp_type'];


    public function items()
    {
        return $this->hasMany('App\CPRulesItem', 'pcp_id');
    }

    public function program()
    {
        return $this->hasMany(Practice::class, 'id', 'prov_id');
    }

    public function getCPRulesPCPForProv($provId)
    {
        $CPrulesPCP = CPRulesPCP::where('prov_id', '=', $provId)->get();

        return $CPrulesPCP;
    }
}
