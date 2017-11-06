<?php namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\UserMeta
 *
 * @property int $umeta_id
 * @property int $user_id
 * @property string|null $meta_key
 * @property string|null $meta_value
 * @property-read \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
 * @property-read \App\User $wpUser
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserMeta whereMetaKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserMeta whereMetaValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserMeta whereUmetaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\UserMeta whereUserId($value)
 * @mixin \Eloquent
 */
class UserMeta extends \App\BaseModel
{

    // for revisionable
    use \Venturecraft\Revisionable\RevisionableTrait;
    public $timestamps = false;
    protected $revisionCreationsEnabled = true;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'usermeta';
    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'umeta_id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['umeta_id', 'user_id', 'meta_key', 'meta_value'];

    public static function boot()
    {
        parent::boot();
    }

    // for revisionable

    public function wpUser()
    {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }
}
