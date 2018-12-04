<?php namespace App\Models\CPM;

use App\User;
use App\Models\CPM\CpmSymptom;
use App\Models\CPM\CpmInstruction;
use App\Contracts\Serviceable;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\CPM\CpmSymptomUser
 *
 * @property int $id
 * @property int $cpm_symptom_id
 * @property int|null $cpm_instruction_id
 * @property int|null $patient_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \App\Models\CPM\CpmSymptom $cpmSymptom
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\CPM\CpmInstruction[] $cpmInstructions
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\User[] $user
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmSymptomUser whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmSymptomUser whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmSymptomUser whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CpmSymptomUser extends \App\BaseModel
{
    use Instructable;

    protected $guarded = [];
    protected $table = 'cpm_symptoms_users';

    public function cpmSymptom()
    {
        return $this->belongsTo(CpmSymptom::class);
    }
    
    public function user()
    {
        return $this->belongsTo(User::class, 'patient_id');
    }
    
    public function instruction()
    {
        return $this->belongsTo(CpmInstruction::class);
    }
}
