<?php namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\CarePerson
 *
 * @property int $id
 * @property int $alert
 * @property int $user_id
 * @property int $member_user_id
 * @property string $type
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \App\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePerson whereAlert($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePerson whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePerson whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePerson whereMemberUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePerson whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePerson whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePerson whereUserId($value)
 * @mixin \Eloquent
 */
class CarePerson extends \App\BaseModel
{

    const BILLING_PROVIDER = 'billing_provider';
    const IN_ADDITION_TO_BILLING_PROVIDER = 'in_addition_to_billing_provider';
    const INSTEAD_OF_BILLING_PROVIDER = 'instead_of_billing_provider';


    const LEAD_CONTACT = 'lead_contact';
    const MEMBER = 'member';
    const SEND_ALERT_TO = 'send_alert_to';
    const EXTERNAL = 'external';
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
    protected $table = 'patient_care_team_members';

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
        'member_user_id',
        'type',
        'alert',
    ];

    // START RELATIONSHIPS
    public function user()
    {
        return $this->belongsTo(User::class, 'member_user_id', 'id');
    }
    // END RELATIONSHIPS
}
