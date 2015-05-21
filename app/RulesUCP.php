<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class RulesUCP extends Model {

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
    protected $table = 'rules_ucp';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'ucp_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['ucp_id', 'items_id', 'user_id', 'meta_key', 'meta_value'];


    public function item()
    {
        return $this->hasOne('App\RulesItem', 'items_id');
    }

    public function getRulesUCP($userId)
    {
        $rulesUCP = RulesUCP::where('user_id', '=', $userId)->get();

        return $rulesUCP;
    }

    public function getRulesUCPDetails($userId)
    {
        $rulesUCP = RulesUCP::where('user_id', '=', $userId)->get();

        foreach ( $rulesUCP as $rules )
        {
            $rules['item'] = $rules->item;
        }

        return $rulesUCP;
    }

}
