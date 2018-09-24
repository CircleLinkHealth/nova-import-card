<?php namespace App;

use App\Contracts\PdfReport;
use App\Contracts\ReportFormatter;
use App\Models\Pdf;
use App\Notifications\CarePlanProviderApproved;
use App\Notifications\Channels\DirectMailChannel;
use App\Notifications\Channels\FaxChannel;
use App\Rules\HasAtLeast2CcmOr1BhiProblems;
use App\Services\CareplanService;
use App\Services\PdfService;
use App\Traits\PdfReportTrait;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Log;
use Validator;

/**
 * App\CarePlan
 *
 * @property int $id
 * @property string $mode
 * @property int $user_id
 * @property int|null $provider_approver_id
 * @property int|null $qa_approver_id
 * @property int $care_plan_template_id
 * @property string $type
 * @property string $status
 * @property \Carbon\Carbon $qa_date
 * @property \Carbon\Carbon $provider_date
 * @property string|null $last_printed
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property-read \App\CarePlanTemplate $carePlanTemplate
 * @property-read \App\CareplanAssessment $assessment
 * @property-read \App\User $patient
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Models\Pdf[] $pdfs
 * @property-read \App\User|null $providerApproverUser
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlan whereCarePlanTemplateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlan whereLastPrinted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlan whereMode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlan whereProviderApproverId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlan whereProviderDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlan whereQaApproverId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlan whereQaDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlan whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlan whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlan whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlan whereUserId($value)
 * @mixin \Eloquent
 */
class CarePlan extends BaseModel implements PdfReport
{
    use PdfReportTrait;

    // status options
    const DRAFT = 'draft';
    const QA_APPROVED = 'qa_approved';
    const PROVIDER_APPROVED = 'provider_approved';

    // mode options
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
        'first_printed_by',
        'first_printed',
        'last_printed',
        'created_at',
        'updated_at',
    ];

    protected $dates = [
        'qa_date',
        'provider_date',
        'first_printed',
    ];

    protected $attributes = [
        'mode' => self::WEB,
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
                                            $q->whereStatus(CarePlan::QA_APPROVED);
                                        })
                                        ->whereHas('patientInfo', function ($q) {
                                            $q->whereCcmStatus(Patient::ENROLLED);
                                        })
                                        ->whereHas('careTeamMembers', function ($q) use (
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

    public function providerApproverUser()
    {
        return $this->belongsTo(User::class, 'provider_approver_id', 'id');
    }

    public function assessment()
    {
        return $this->hasOne(CareplanAssessment::class, 'careplan_id');
    }

    /**
     * Create a PDF of this resource and return the path to it.
     *
     * @param null $scale
     *
     * @return string
     */
    public function toPdf($scale = null): string
    {
        /**
         * Unit tests fail due to an error with generating the PDF.
         * The error is `Exit with code 1 due to http error: 1005`
         * The error happens at random.
         * Below fixes it.
         */
        if (app()->environment('testing')) {
            return public_path('assets/pdf/sample-note.pdf');
        }

        $pdfService      = app(PdfService::class);
        $reportFormatter = app(ReportFormatter::class);
        $careplanService = app(CareplanService::class);

        $careplan = $reportFormatter->formatDataForViewPrintCareplanReport([$this->patient]);
        $careplan = $careplan[$this->patient->id];

        if (empty($careplan)) {
            throw new \Exception("Could not get CarePlan info for CarePlan with ID: $this->id");
        }

        return $pdfService->createPdfFromView('wpUsers.patient.multiview', [
            'careplans'    => [$this->patient->id => $careplan],
            'isPdf'        => true,
            'letter'       => false,
            'problemNames' => $careplan['problem'],
            'careTeam'     => $this->patient->careTeamMembers,
            'data'         => $careplanService->careplan($this->patient->id),
        ], [
            'disable-javascript' => true,
        ]);
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

    /**
     * Get the name of the provider who approved this care plan
     *
     * @return string
     */
    public function getProviderApproverNameAttribute()
    {
        $approver = $this->providerApproverUser;

        return $approver
            ? $approver->fullName
            : '';
    }

    public function safe()
    {
        return [
            'id'      => $this->id,
            'user_id' => $this->user_id,
            'status'  => $this->status,
            'mode'    => $this->mode,
            'type'    => $this->type,
        ];
    }

    /**
     * Get the URL to view the CarePlan
     *
     * @return string
     */
    public function link()
    {
        return route('patient.careplan.print', [
            'patientId' => $this->user_id,
        ]);
    }

    /**
     * Forwards CarePlan to CareTeam and/or Support
     */
    public function forward()
    {
        Log::debug("CarePlan: Ready to forward");

        $this->load([
            'patient.primaryPractice.settings',
            'patient.patientInfo.location',
        ]);

        $cpmSettings = $this->patient->primaryPractice->cpmSettings();

        $channels = [];

        if ($cpmSettings->efax_pdf_careplan) {
            $channels[] = FaxChannel::class;
            Log::debug("CarePlan: Will forward to fax");
        }

        if ($cpmSettings->dm_pdf_careplan) {
            $channels[] = DirectMailChannel::class;
            Log::debug("CarePlan: Will forward to direct mail");
        }

        if (empty($channels)) {
            $patientId = $this->patient->id;
            $practice  = $this->patient->primaryPractice->name;
            Log::debug("CarePlan: Will not be forwarded because primary practice[$practice] for patient[$patientId] does not have any enabled channels.");

            return;
        }

        $location = $this->patient->patientInfo->location;
        if ($location == null) {
            $patientId = $this->patient->id;
            Log::debug("CarePlan: Will not be forwarded because patient[$patientId] does not have a preferred contact location.");

            return;
        }

        $location->notify(new CarePlanProviderApproved($this, $channels));
    }

    /**
     * Returns the notifications that included this resource as an attachment
     *
     * @return MorphMany
     */
    public function notifications()
    {
        return $this->morphMany(DatabaseNotification::class, 'attachment')
                    ->orderBy('created_at', 'desc');
    }

    /**
     * Validate that the recently created CarePlan has all the data CLH needs to provide services to a patient.
     *
     * @return \Illuminate\Validation\Validator
     */
    public function validator()
    {
        $patient = $this->patient->load([
            'patientInfo',
            'phoneNumbers',
            'billingProvider.user',
            'ccdProblems' => function ($q) {
                return $q->has('cpmProblem')
                         ->with('cpmProblem');
            },
            //before enabling insurance validation, we have to store all insurance info in CPM
            //            'ccdInsurancePolicies',
        ]);

        $data = [
            'conditions'      => $patient->ccdProblems,
            //before enabling insurance validation, we have to store all insurance info in CPM
            //            'insurances' => $patient->ccdInsurancePolicies,
            'phoneNumber'     => optional($patient->phoneNumbers->first())->number,
            'dob'             => $patient->patientInfo->birth_date,
            'mrn'             => $patient->patientInfo->mrn_number,
            'name'            => $patient->full_name,
            'billingProvider' => optional($patient->billingProviderUser())->id,
        ];

        return Validator::make($data, [
            'conditions'      => [new HasAtLeast2CcmOr1BhiProblems()],
            'phoneNumber'     => 'required|phone:AUTO,US',
            'dob'             => 'required|date',
            'mrn'             => 'required',
            'name'            => 'required',
            'billingProvider' => 'required|numeric',
        ]);
    }
}
