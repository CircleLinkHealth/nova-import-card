<?php namespace App;

use App\Contracts\PdfReport;
use App\Services\ReportsService;
use App\Traits\PdfReportTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

class CarePlan extends Model implements PdfReport
{
    use PdfReportTrait;

    protected $fillable = [
        'user_id',
        'provider_approver_id',
        'qa_approver_id',
        'care_plan_template_id',
        'type',
        'status',
        'qa_date',
        'provider_date',
        'last_printed',
        'created_at',
        'updated_at',
    ];

    public static function getNumberOfCareplansPendingApproval(User $user)
    {
        $pendingApprovals = 0;

        if ($user->hasRole([
            'administrator',
            'care-center',
        ])
        ) {
            $pendingApprovals = User::ofType('participant')
                ->intersectPracticesWith($user)
                ->whereHas('carePlan', function ($q) {
                    $q->whereStatus('draft');
                })
                ->count();
        } else {
            if ($user->hasRole(['provider'])) {
                $pendingApprovals = User::ofType('participant')
                    ->intersectPracticesWith($user)
                    ->whereHas('carePlan', function ($q) {
                        $q->whereStatus('qa_approved');
                    })
                    ->whereHas('patientInfo', function ($q) {
                        $q->whereCcmStatus('enrolled');
                    })
                    ->whereHas('patientCareTeamMembers', function ($q) use
                    (
                        $user
                    ) {
                        $q->where('member_user_id', '=', $user->id)
                            ->where('type', '=', PatientCareTeamMember::BILLING_PROVIDER);
                    })
                    ->count();
            }
        }

        return $pendingApprovals;
    }

    public function carePlanTemplate()
    {
        return $this->belongsTo(CarePlanTemplate::class);
    }

    public function patient()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function getCarePlanTemplateIdAttribute()
    {
        //@todo: pretty sure that's not the way it's done. come back here later
        return $this->attributes['care_plan_template_id'];
    }

    public function providerApproverUser()
    {
        return $this->belongsTo(User::class, 'provider_approver_id', 'id');
    }

    /**
     * Create a PDF of this resource and return the path to it.
     *
     * @return string
     */
    public function toPdf() : string
    {
        $user = $this->patient;

        $careplan = (new ReportsService())->carePlanGenerator([$user]);

        $pdf = App::make('snappy.pdf.wrapper');
        $pdf->loadView('wpUsers.patient.careplan.print', [
            'patient'             => $user,
            'problems'            => $careplan[$user->id]['problems'],
            'biometrics'          => $careplan[$user->id]['bio_data'],
            'symptoms'            => $careplan[$user->id]['symptoms'],
            'lifestyle'           => $careplan[$user->id]['lifestyle'],
            'medications_monitor' => $careplan[$user->id]['medications'],
            'taking_medications'  => $careplan[$user->id]['taking_meds'],
            'allergies'           => $careplan[$user->id]['allergies'],
            'social'              => $careplan[$user->id]['social'],
            'appointments'        => $careplan[$user->id]['appointments'],
            'other'               => $careplan[$user->id]['other'],
            'isPdf'               => true,
        ]);

        $file_name = base_path('storage/pdfs/careplans/' . Carbon::now()->toDateString() . '-' . str_random(40) . '.pdf');
        $pdf->save($file_name, true);

        return $file_name;
    }
}
