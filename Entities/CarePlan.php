<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Entities;

use App\Constants;
use App\Contracts\PdfReport;
use App\Contracts\ReportFormatter;
use App\Note;
use App\Notifications\CarePlanProviderApproved;
use App\Notifications\Channels\DirectMailChannel;
use App\Notifications\NotifyPatientCarePlanApproved;
use App\Rules\HasEnoughProblems;
use App\Services\Calls\SchedulerService;
use App\Services\CareplanService;
use CircleLinkHealth\Core\Entities\BaseModel;
use CircleLinkHealth\Core\Services\PdfService;
use CircleLinkHealth\Customer\Entities\CarePerson;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Rules\PatientIsNotDuplicate;
use CircleLinkHealth\Customer\Traits\PdfReportTrait;
use CircleLinkHealth\Eligibility\CcdaImporter\Tasks\ImportPatientInfo;
use CircleLinkHealth\Eligibility\Rules\MrnWasReplacedIfPracticeImportingHooks;
use CircleLinkHealth\SharedModels\Rules\DoesNotHaveBothTypesOfDiabetes;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Log;
use Validator;

/**
 * CircleLinkHealth\SharedModels\Entities\CarePlan.
 *
 * @property int                                                                                    $id
 * @property string                                                                                 $mode
 * @property int                                                                                    $user_id
 * @property int|null                                                                               $provider_approver_id
 * @property int|null                                                                               $qa_approver_id
 * @property int                                                                                    $care_plan_template_id
 * @property string                                                                                 $type
 * @property string                                                                                 $status
 * @property \Carbon\Carbon                                                                         $qa_date
 * @property \Carbon\Carbon                                                                         $provider_date
 * @property string|null                                                                            $last_printed
 * @property \Carbon\Carbon                                                                         $created_at
 * @property \Carbon\Carbon                                                                         $updated_at
 * @property \CircleLinkHealth\SharedModels\Entities\CarePlanTemplate                               $carePlanTemplate
 * @property \App\CareplanAssessment                                                                $assessment
 * @property \CircleLinkHealth\Customer\Entities\User                                               $patient
 * @property \CircleLinkHealth\SharedModels\Entities\Pdf[]|\Illuminate\Database\Eloquent\Collection $pdfs
 * @property \CircleLinkHealth\Customer\Entities\User|null                                          $providerApproverUser
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\CarePlan
 *     whereCarePlanTemplateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\CarePlan
 *     whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\CarePlan
 *     whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\CarePlan
 *     whereLastPrinted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\CarePlan
 *     whereMode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\CarePlan
 *     whereProviderApproverId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\CarePlan
 *     whereProviderDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\CarePlan
 *     whereQaApproverId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\CarePlan
 *     whereQaDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\CarePlan
 *     whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\CarePlan
 *     whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\CarePlan
 *     whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\CarePlan
 *     whereUserId($value)
 * @mixin \Eloquent
 * @property int|null                        $first_printed_by
 * @property \Illuminate\Support\Carbon|null $first_printed
 * @property string                          $provider_approver_name
 * @property \CircleLinkHealth\Core\Entities\DatabaseNotification[]|\Illuminate\Notifications\DatabaseNotificationCollection
 *     $notifications
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection
 *     $revisionHistory
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\CarePlan
 *     newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\CarePlan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\CarePlan query()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\CarePlan
 *     whereFirstPrinted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\CarePlan
 *     whereFirstPrintedBy($value)
 * @property int|null                        $notifications_count
 * @property int|null                        $pdfs_count
 * @property int|null                        $revision_history_count
 * @property string|null                     $deleted_at
 * @method   static                          bool|null forceDelete()
 * @method   static                          \Illuminate\Database\Query\Builder|\CircleLinkHealth\SharedModels\Entities\CarePlan onlyTrashed()
 * @method   static                          bool|null restore()
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\CarePlan whereDeletedAt($value)
 * @method   static                          \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\SharedModels\Entities\CarePlan withNurseApprovedVia()
 * @method   static                          \Illuminate\Database\Query\Builder|\CircleLinkHealth\SharedModels\Entities\CarePlan withTrashed()
 * @method   static                          \Illuminate\Database\Query\Builder|\CircleLinkHealth\SharedModels\Entities\CarePlan withoutTrashed()
 * @property \Illuminate\Support\Carbon|null $rn_date
 * @property int|null                        $rn_approver_id
 */
class CarePlan extends BaseModel implements PdfReport
{
    use PdfReportTrait;
    use SoftDeletes;

    // status options
    const DRAFT                    = 'draft';
    const PDF                      = 'pdf';
    const PROVIDER_APPROVED        = 'provider_approved';
    const QA_APPROVED              = 'qa_approved';
    const RN_APPROVAL_RELEASE_DATE = '2020-07-27';
    const RN_APPROVED              = 'rn_approved';

    // mode options
    const WEB = 'web';

    protected $attributes = [
        'mode' => self::WEB,
    ];

    protected $dates = [
        'qa_date',
        'rn_date',
        'provider_date',
        'first_printed',
    ];

    protected $fillable = [
        'user_id',
        'mode',
        'provider_approver_id',
        'qa_approver_id',
        'rn_approver_id',
        'care_plan_template_id',
        'type',
        'status',
        'qa_date',
        'rn_date',
        'provider_date',
        'first_printed_by',
        'first_printed',
        'last_printed',
        'created_at',
        'updated_at',
    ];

    public function carePlanTemplate()
    {
        return $this->belongsTo(CarePlanTemplate::class);
    }

    /**
     * Forwards CarePlan to CareTeam and/or Support.
     */
    public function forward()
    {
        Log::debug('CarePlan: Ready to forward');

        $this->load(
            [
                'patient.primaryPractice.settings',
                'patient.patientInfo.location',
            ]
        );

        $channels = $this->notificationChannels();

        if (empty($channels)) {
            $patientId = $this->patient->id;
            $practice  = $this->patient->primaryPractice->name;
            Log::error(
                "CarePlan: Will not be forwarded because primary practice[${practice}] for patient[${patientId}] does not have any enabled channels."
            );

            return;
        }

        $location = $this->patient->patientInfo->location;
        if (null == $location) {
            $patientId = $this->patient->id;
            Log::error(
                "CarePlan: Will not be forwarded because patient[${patientId}] does not have a preferred contact location."
            );

            return;
        }

        $location->notify(new CarePlanProviderApproved($this, $channels));
    }

    public static function getNumberOfCareplansPendingApproval(User $user)
    {
        $pendingApprovals = 0;

        if ($user->hasRole(
            [
                'administrator',
                'care-center',
            ]
        )
        ) {
            $pendingApprovals = User::ofType('participant')
                ->intersectPracticesWith($user)
                ->whereHas(
                    'carePlan',
                    function ($q) {
                        $q->whereStatus('draft');
                    }
                )
                ->count();
        } else {
            if ($user->isProvider()) {
                $pendingApprovals = User::ofType('participant')
                    ->intersectPracticesWith($user)
                    ->whereHas(
                        'carePlan',
                        function ($q) {
                            $q->whereStatus(CarePlan::RN_APPROVED);
                        }
                    )
                    ->whereHas(
                        'patientInfo',
                        function ($q) {
                            $q->whereCcmStatus(Patient::ENROLLED);
                        }
                    )
                    ->whereHas(
                        'careTeamMembers',
                        function ($q) use (
                                                $user
                                            ) {
                            $q->where('member_user_id', '=', $user->id)
                                ->where('type', '=', CarePerson::BILLING_PROVIDER);
                        }
                    )
                    ->count();
            }
        }

        return $pendingApprovals;
    }

    public function getNurseApproverName()
    {
        $note = $this->patient->notes->firstWhere(
            'type',
            '=',
            SchedulerService::PROVIDER_REQUEST_FOR_CAREPLAN_APPROVAL_TYPE
        );

        if ( ! $note) {
            return null;
        }

        $call = $note->call;

        if ( ! $call) {
            return null;
        }

        $outboundUser = $call->outboundUser;

        if ( ! $outboundUser) {
            return null;
        }

        return $outboundUser->getFullName();
    }

    /**
     * Get the name of the provider who approved this care plan.
     *
     * @return string
     */
    public function getProviderApproverNameAttribute()
    {
        $approver = $this->providerApproverUser;

        return $approver
            ? $approver->getFullName()
            : '';
    }

    public function isClhAdminApproved(): bool
    {
        return CarePlan::QA_APPROVED == $this->status;
    }

    public function isProviderApproved()
    {
        return CarePlan::PROVIDER_APPROVED == $this->status;
    }

    public function isRnApprovalEnabled(): bool
    {
        return $this->created_at->isAfter(self::RN_APPROVAL_RELEASE_DATE);
    }

    public function isRnApproved()
    {
        return CarePlan::RN_APPROVED == $this->status;
    }

    /**
     * Get the URL to view the CarePlan.
     *
     * @return string
     */
    public function link()
    {
        return route(
            'patient.careplan.print',
            [
                'patientId' => $this->user_id,
            ]
        );
    }

    /**
     * @return array
     */
    public function notificationChannels()
    {
        $channels = ['database'];

        $cpmSettings = $this->patient->primaryPractice->cpmSettings();

        if ($cpmSettings->efax_pdf_careplan) {
            $channels[] = 'phaxio';
        }

        if ($cpmSettings->dm_pdf_careplan) {
            $channels[] = DirectMailChannel::class;
        }

        return $channels;
    }

    /**
     * Returns the notifications that included this resource as an attachment.
     *
     * @return MorphMany
     */
    public function notifications()
    {
        return $this->morphMany(\CircleLinkHealth\Core\Entities\DatabaseNotification::class, 'attachment')
            ->orderBy('created_at', 'desc');
    }

    public function notifyPatientOfApproval()
    {
        if ( ! patientLoginIsEnabledForPractice($this->patient->program_id)) {
            return;
        }

        $this->patient->notify(new NotifyPatientCarePlanApproved($this));
    }

    public function patient()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get all the PDF CarePlans attached to this CarePlan.
     */
    public function pdfs()
    {
        return $this->morphMany(Pdf::class, 'pdfable');
    }

    public function providerApproverUser()
    {
        return $this->belongsTo(User::class, 'provider_approver_id', 'id');
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

    public function scopeWithNurseApprovedVia($query)
    {
        $query->with(
            [
                'patient.notes' => function ($q) {
                    $q->where('type', SchedulerService::PROVIDER_REQUEST_FOR_CAREPLAN_APPROVAL_TYPE)->with(
                        'call.outboundUser'
                    );
                },
            ]
        );
    }

    public function shouldRnApprove(User $currentUser): bool
    {
        return $this->isRnApprovalEnabled()
            && CarePlan::QA_APPROVED === $this->status
            && $currentUser->isCareCoach()
            && $currentUser->canRNApproveCarePlans();
    }

    /**
     * Should "Approve" button be shown on "View CarePlan" page?
     */
    public function shouldShowApprovalButton(): bool
    {
        /** @var User $user */
        $user = auth()->user();
        if (self::RN_APPROVED === $this->status && $user->canApproveCarePlans()) {
            return true;
        }

        if (self::QA_APPROVED === $this->status && $user->canRNApproveCarePlans()) {
            return true;
        }

        if (self::DRAFT === $this->status && $user->canQAApproveCarePlans()) {
            return true;
        }

        return false;
    }

    /**
     * Create a PDF of this resource and return the path to it.
     *
     * @param null $scale
     */
    public function toPdf($scale = null): string
    {
        /*
         * Unit tests fail due to an error with generating the PDF.
         * The error is `Exit with code 1 due to http error: 1005`
         * The error happens at random.
         * Below fixes it.
         */
        if (isUnitTestingEnv()) {
            return public_path('assets/pdf/sample-note.pdf');
        }

        $pdfService      = app(PdfService::class);
        $reportFormatter = app(ReportFormatter::class);
        $careplanService = app(CareplanService::class);

        $careplan = $reportFormatter->formatDataForViewPrintCareplanReport($this->patient);
        $careplan = $careplan[$this->patient->id];

        if (empty($careplan)) {
            throw new \Exception("Could not get CarePlan info for CarePlan with ID: {$this->id}");
        }

        $patient       = $this->patient;
        $billingDoctor = $patient->billingProviderUser();
        $regularDoctor = $patient->regularDoctorUser();

        return $pdfService->createPdfFromView(
            'wpUsers.patient.multiview',
            [
                'careplans'     => [$this->patient->id => $careplan],
                'isPdf'         => true,
                'letter'        => false,
                'problemNames'  => $careplan['problem'],
                'careTeam'      => $this->patient->careTeamMembers,
                'data'          => $careplanService->careplan($this->patient->id),
                'patient'       => $patient,
                'billingDoctor' => $billingDoctor,
                'regularDoctor' => $regularDoctor,
            ],
            null,
            Constants::SNAPPY_CLH_MAIL_VENDOR_SETTINGS
        );
    }

    /**
     * Validate that the recently created CarePlan has all the data CLH needs to provide services to a patient.
     *
     * @throws \Exception
     */
    public function validator(bool $confirmDiabetesConditions = false): \Illuminate\Validation\Validator
    {
        $patient = $this->patient->load(
            [
                'patientInfo',
                'phoneNumbers',
                'billingProvider.user',
                'ccdProblems' => function ($q) {
                    return $q->has('cpmProblem')
                        ->with('cpmProblem');
                },
                'ccdMedications',
                //before enabling insurance validation, we have to store all insurance info in CPM
                //            'ccdInsurancePolicies',
            ]
        );

        $data = [
            'conditions' => $patient->ccdProblems,
            //before enabling insurance validation, we have to store all insurance info in CPM
            //            'insurances' => $patient->ccdInsurancePolicies,
            'medications'     => $patient->ccdMedications,
            'phoneNumber'     => optional($patient->phoneNumbers->first())->number,
            'dob'             => $patient->getBirthDate(),
            'mrn'             => $patient->getMRN(),
            'name'            => $patient->getFullName(),
            'billingProvider' => optional($patient->billingProviderUser())->id,
            'practice'        => $patient->program_id,
            'location'        => $patient->getPreferredContactLocation(),
            'duplicate'       => $patient->getMRN(),
            'address'         => $patient->address,
            'city'            => $patient->city,
            'state'           => $patient->state,
            'zip'             => $patient->zip,
        ];

        return Validator::make(
            $data,
            [
                'conditions' => [
                    new HasEnoughProblems($this->patient),
                    //If Approver has confirmed that Diabetes Conditions are correct or if Care Plan has already been approved, bypass check
                    ! $confirmDiabetesConditions
                        ? new DoesNotHaveBothTypesOfDiabetes()
                        : null,
                ],
                'medications'     => 'required|filled',
                'phoneNumber'     => 'required|phone:AUTO,US',
                'dob'             => 'required|date',
                'mrn'             => ['required', new MrnWasReplacedIfPracticeImportingHooks($patient)],
                'name'            => 'required',
                'billingProvider' => 'required|numeric',
                'practice'        => 'required|numeric',
                'location'        => 'required|numeric',
                'duplicate'       => [new PatientIsNotDuplicate($this->patient->program_id, $this->patient->first_name, $this->patient->last_name, ImportPatientInfo::parseDOBDate($this->patient->patientInfo->birth_date), $this->patient->patientInfo->mrn_number, $this->patient->id)],
                'address'         => 'required|filled',
                'city'            => 'required|filled',
                'state'           => 'required|filled',
                'zip'             => 'required|filled',
            ],
            [
                'phoneNumber.phone' => 'The patient has an invalid phone number.',
            ]
        );
    }

    public function wasApprovedViaNurse()
    {
        return Note::where('patient_id', $this->user_id)->where(
            'type',
            SchedulerService::PROVIDER_REQUEST_FOR_CAREPLAN_APPROVAL_TYPE
        )->exists();
    }
}
