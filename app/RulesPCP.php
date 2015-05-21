<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class RulesPCP extends Model {

    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'mysql_no_prefix';

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


    public function getRulesPCPForProv($provId)
    {
        $rulesPCP = RulesPCP::where('prov_id', '=', $provId)->get();

        return $rulesPCP;
    }

}
