<?php namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\CPRulesUCP
 *
 * @property int $ucp_id
 * @property int|null $items_id
 * @property int|null $user_id
 * @property string|null $meta_key
 * @property string|null $meta_value
 * @property-read \App\CPRulesItem|null $item
 * @property-read \App\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesUCP whereItemsId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesUCP whereMetaKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesUCP whereMetaValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesUCP whereUcpId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesUCP whereUserId($value)
 * @mixin \Eloquent
 */
class CPRulesUCP extends \App\BaseModel
{
    public $timestamps = false;



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
        return $this->belongsTo('App\CPRulesItem', 'items_id');
    }

    public function user()
    {
        return $this->hasOne('App\User', 'user_id');
    }

    public function getCPRulesUCP($userId)
    {
        $rulesUCP = CPRulesUCP::where('user_id', '=', $userId)->get();

        return $rulesUCP;
    }

    public function getCPRulesUCPDetails($userId)
    {
        $rulesUCP = CPRulesUCP::where('user_id', '=', $userId)->get();

        foreach ($rulesUCP as $rules) {
            $rules['item'] = $rules->item;
        }

        return $rulesUCP;
    }
}
