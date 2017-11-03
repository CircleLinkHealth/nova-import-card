<?php namespace App;

use App\Contracts\PdfReport;
use App\Models\Pdf;
use App\Services\ReportsService;
use App\Traits\PdfReportTrait;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;

class CarePlan extends \App\BaseModel implements PdfReport
{
    use PdfReportTrait;

    // statuses
    const DRAFT = 'draft';
    const QA_APPROVED = 'qa_approved';
    const PROVIDER_APPROVED = 'provider_approved';

    // modes
    const WEB = 'web';
    const PDF = 'pdf';

    protected $fillable = [
        'user_id',
        'mode',
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

    protected $dates = [
        'qa_date',
        'provider_date',
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
                    ->whereHas('careTeamMembers', function ($q) use
                        (
                        $user
                    ) {
                        $q->where('member_user_id', '=', $user->id)
                            ->where('type', '=', CarePerson::BILLING_PROVIDER);
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
            'problemNames'        => $user->cpmProblems()->get()->sortBy('name')->pluck('name')->all(),
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

        $file_name = base_path('storage/pdfs/careplans/' . Carbon::now()->toDateString() . '-' . $user->fullName . '.pdf');
        $pdf->save($file_name, true);

        return $file_name;
    }

    public function isProviderApproved()
    {
        return $this->status == CarePlan::PROVIDER_APPROVED;
    }

    /**
     * Get all the PDF CarePlans attached to this CarePlan.
     */
    public function pdfs()
    {
        return $this->morphMany(Pdf::class, 'pdfable');
    }
}
