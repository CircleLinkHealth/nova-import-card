<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class PhoneNumber extends Model
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
