<?php namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\PhoneNumber
 *
 * @property int $id
 * @property int $user_id
 * @property int $location_id
 * @property string|null $number
 * @property string|null $extension
 * @property string|null $type
 * @property int $is_primary
 * @property string $created_at
 * @property string $updated_at
 * @property string|null $deleted_at
 * @property-read \App\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PhoneNumber whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PhoneNumber whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PhoneNumber whereExtension($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PhoneNumber whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PhoneNumber whereIsPrimary($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PhoneNumber whereLocationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PhoneNumber whereNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PhoneNumber whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PhoneNumber whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\PhoneNumber whereUserId($value)
 * @mixin \Eloquent
 */
class PhoneNumber extends \App\BaseModel
{

    //types
    const HOME = 'home';
    const MOBILE = 'mobile';
    const WORK = 'work';

    public $timestamps = false;
    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'mysql';
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'phone_numbers';
    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'location_id',
        'number',
        'type',
        'is_primary',
        'extension',
    ];

    public static function getTypes() : array
    {
        return [
            1 => PhoneNumber::HOME,
            2 => PhoneNumber::MOBILE,
            3 => PhoneNumber::WORK,
        ];
    }

    // START RELATIONSHIPS

    public function user()
    {
        return $this->belongsTo('App\User', 'id', 'user_id');
    }
    // END RELATIONSHIPS
}
