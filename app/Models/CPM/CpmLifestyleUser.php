<?php namespace App\Models\CPM;

use App\Contracts\Serviceable;
use App\Models\CPM\CpmLifestyle;
use App\User;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\CPM\CpmLifestyleUser
 *
 * @property int $id
 * @property int|null $cpm_instruction_id
 * @property int $patient_id
 * @property int $cpm_lifestyle_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read App\Models\CPM\CpmInstruction $cpmInstruction
 * @property-read \App\User $patient
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmLifestyle whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmLifestyle whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmLifestyle whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmLifestyle whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CpmLifestyle extends \App\BaseModel
{
    
    use Instructable;

    protected $guarded = [];

    public function instruction()
    {
        return $this->hasOne(CpmInstruction::class, 'cpm_instruction_id');
    }

    public function patient()
    {
        return $this->hasOne(CpmLifestyleUser::class, 'cpm_lifestyle_id');
    }
}
