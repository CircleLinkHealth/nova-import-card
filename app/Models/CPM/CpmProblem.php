<?php namespace App\Models\CPM;

use App\CareItem;
use App\CarePlanItem;
use App\CarePlanTemplate;
use App\Contracts\Serviceable;
use App\Services\CPM\CpmProblemService;
use App\User;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\CPM\CpmProblem
 *
 * @property int $id
 * @property string $default_icd_10_code
 * @property string $name
 * @property string $icd10from
 * @property string $icd10to
 * @property float $icd9from
 * @property float $icd9to
 * @property string $contains
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \App\CareItem $carePlanItemIdDeprecated
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\CarePlanTemplate[] $carePlanTemplates
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\CPM\CpmBiometric[] $cpmBiometricsToBeActivated
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\CPM\CpmInstruction[] $cpmInstructions
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\CPM\CpmLifestyle[] $cpmLifestylesToBeActivated
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\CPM\CpmMedicationGroup[] $cpmMedicationGroupsToBeActivated
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\CPM\CpmSymptom[] $cpmSymptomsToBeActivated
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\User[] $patient
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmProblem whereContains($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmProblem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmProblem whereDefaultIcd10Code($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmProblem whereIcd10from($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmProblem whereIcd10to($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmProblem whereIcd9from($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmProblem whereIcd9to($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmProblem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmProblem whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Models\CPM\CpmProblem whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CpmProblem extends \App\BaseModel implements Serviceable
{
    
    use Instructable;

    protected $table = 'cpm_problems';

    protected $guarded = [];
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function carePlanTemplates()
    {
        return $this->belongsToMany(CarePlanTemplate::class, 'care_plan_templates_cpm_problems');
    }

    public function carePlanItemIdDeprecated()
    {
        return $this->belongsTo(CareItem::class);
    }

    /**
     * During the CCD Importing process, if a patient has this problem, we will be creating a relationship between
     * the patient and this CpmEntity. In other words, this can activate this CpmEntity for the patient.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function cpmBiometricsToBeActivated()
    {
        return $this->belongsToMany(CpmBiometric::class, 'cpm_problems_activate_cpm_biometrics')
            ->withPivot('care_plan_template_id')
            ->withTimestamps();
    }

    /**
     * During the CCD Importing process, if a patient has this problem, we will be creating a relationship between
     * the patient and this CpmEntity. In other words, this can activate this CpmEntity for the patient.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function cpmLifestylesToBeActivated()
    {
        return $this->belongsToMany(CpmLifestyle::class, 'cpm_problems_activate_cpm_lifestyles')
            ->withPivot('care_plan_template_id')
            ->withTimestamps();
    }

    /**
     * During the CCD Importing process, if a patient has this problem, we will be creating a relationship between
     * the patient and this CpmEntity. In other words, this can activate this CpmEntity for the patient.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function cpmMedicationGroupsToBeActivated()
    {
        return $this->belongsToMany(CpmMedicationGroup::class, 'cpm_problems_activate_cpm_medication_groups')
            ->withPivot('care_plan_template_id')
            ->withTimestamps();
    }

    /**
     * During the CCD Importing process, if a patient has this problem, we will be creating a relationship between
     * the patient and this CpmEntity. In other words, this can activate this CpmEntity for the patient.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function cpmSymptomsToBeActivated()
    {
        return $this->belongsToMany(CpmSymptom::class, 'cpm_problems_activate_cpm_symptoms')
            ->withPivot('care_plan_template_id')
            ->withTimestamps();
    }
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function patient()
    {
        return $this->belongsToMany(User::class, 'cpm_problems_users', 'patient_id');
    }

    /**
     * Get this Model's Service Class
     *
     * @return Serviceable
     */
    public function service()
    {
        return new CpmProblemService();
    }
}
