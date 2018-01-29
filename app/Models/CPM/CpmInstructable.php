<?php namespace App\Models\CPM;

use App\Models\CPM\CpmInstruction;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\CPM\CpmMiscUser
 *
 * @property int $id
 * @property int|null $cpm_instruction_id
 * @property int $instructable_id
 * @property int $instruction_type
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \App\Models\CPM\CpmInstruction $cpmInstruction
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmMisc whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmMisc whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmMisc whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmMisc whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CpmInstructable extends \App\BaseModel
{

    protected $table = 'instructables';

    public function cpmInstruction()
    {
        return $this->belongsTo(CpmInstruction::class, 'cpm_instruction_id');
    }
    
    public function source()
    {
        return $this->belongsTo(app($this->instructable_type), 'instructable_id');
    }
}
