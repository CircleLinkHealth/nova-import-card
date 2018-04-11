<?php namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\CPRulesItemMeta
 *
 * @property int $itemmeta_id
 * @property int|null $items_id
 * @property string|null $meta_key
 * @property string|null $meta_value
 * @property-read \App\CPRulesItem|null $CPRulesItem
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesItemMeta whereItemmetaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesItemMeta whereItemsId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesItemMeta whereMetaKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CPRulesItemMeta whereMetaValue($value)
 * @mixin \Eloquent
 */
class CPRulesItemMeta extends \App\BaseModel
{



    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'rules_itemmeta';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'itemmeta_id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['itemmeta_id', 'items_id', 'meta_key', 'meta_value'];

    public $timestamps = false;

    public function CPRulesItem()
    {
        return $this->belongsTo('App\CPRulesItem', 'items_id');
    }
}
