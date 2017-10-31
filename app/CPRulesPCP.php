<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class CPRulesPCP extends Model
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
