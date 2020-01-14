<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use App\Contracts\PdfReport;
use App\Contracts\ReportFormatter;
use App\Notifications\CarePlanProviderApproved;
use App\Notifications\Channels\DirectMailChannel;
use App\Notifications\Channels\FaxChannel;
use App\Notifications\NotifyPatientCarePlanApproved;
use App\Rules\HasAtLeast2CcmOr1BhiProblems;
use App\Services\CareplanService;
use CircleLinkHealth\Core\PdfService;
use App\Traits\PdfReportTrait;
use CircleLinkHealth\Core\Entities\BaseModel;
use CircleLinkHealth\Customer\Entities\CarePerson;
use CircleLinkHealth\Customer\Entities\Patient;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Eligibility\Rules\HasValidNbiMrn;
use CircleLinkHealth\SharedModels\Entities\Pdf;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Log;
use Validator;

/**
 * App\CarePlan.
 *
 * @property int                                                        $id
 * @property string                                                     $mode
 * @property int                                                        $user_id
 * @property int|null                                                   $provider_approver_id
 * @property int|null                                                   $qa_approver_id
 * @property int                                                        $care_plan_template_id
 * @property string                                                     $type
 * @property string                                                     $status
 * @property \Carbon\Carbon                                             $qa_date
 * @property \Carbon\Carbon                                             $provider_date
 * @property string|null                                                $last_printed
 * @property \Carbon\Carbon                                             $created_at
 * @property \Carbon\Carbon                                             $updated_at
 * @property \App\CarePlanTemplate                                      $carePlanTemplate
 * @property \App\CareplanAssessment                                    $assessment
 * @property \CircleLinkHealth\Customer\Entities\User                   $patient
 * @property \App\Models\Pdf[]|\Illuminate\Database\Eloquent\Collection $pdfs
 * @property \CircleLinkHealth\Customer\Entities\User|null              $providerApproverUser
 *
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
 *
 * @property int|null                                                                                                        $first_printed_by
 * @property \Illuminate\Support\Carbon|null                                                                                 $first_printed
 * @property string                                                                                                          $provider_approver_name
 * @property \CircleLinkHealth\Core\Entities\DatabaseNotification[]|\Illuminate\Notifications\DatabaseNotificationCollection $notifications
 * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[]                                  $revisionHistory
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlan query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlan whereFirstPrinted($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\CarePlan whereFirstPrintedBy($value)
 *
 * @property int|null $notifications_count
 * @property int|null $pdfs_count
 * @property int|null $revision_history_count
 */
class CarePlan extends BaseModel implements PdfReport
{
    use PdfReportTrait;
    
    // status options
    const DRAFT             = 'draft';
    const PDF               = 'pdf';
    const PROVIDER_APPROVED = 'provider_approved';
    const QA_APPROVED       = 'qa_approved';
    
    // mode options
    const WEB = 'web';
    
    protected $attributes = [
        'mode' => self::WEB,
    ];
    
    protected $dates = [
        'qa_date',
        'provider_date',
        'first_printed',
    ];
    
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
    
    public function alertPatientAboutApproval()
    {
        $this->patient->notify(new NotifyPatientCarePlanApproved($this));
    }
    
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
                                                $q->whereStatus(CarePlan::QA_APPROVED);
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
    
    public function isProviderApproved()
    {
        return CarePlan::PROVIDER_APPROVED == $this->status;
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
            $channels[] = FaxChannel::class;
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
        
        return $pdfService->createPdfFromView(
            'wpUsers.patient.multiview',
            [
                'careplans'    => [$this->patient->id => $careplan],
                'isPdf'        => true,
                'letter'       => false,
                'problemNames' => $careplan['problem'],
                'careTeam'     => $this->patient->careTeamMembers,
                'data'         => $careplanService->careplan($this->patient->id),
            ],
            null,
            Constants::SNAPPY_CLH_MAIL_VENDOR_SETTINGS
        );
    }
    
    /**
     * Validate that the recently created CarePlan has all the data CLH needs to provide services to a patient.
     *
     * @return \Illuminate\Validation\Validator
     */
    public function validator()
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
                //before enabling insurance validation, we have to store all insurance info in CPM
                //            'ccdInsurancePolicies',
            ]
        );
        
        $data = [
            'conditions' => $patient->ccdProblems,
            //before enabling insurance validation, we have to store all insurance info in CPM
            //            'insurances' => $patient->ccdInsurancePolicies,
            'phoneNumber'     => optional($patient->phoneNumbers->first())->number,
            'dob'             => $patient->getBirthDate(),
            'mrn'             => $patient->getMRN(),
            'name'            => $patient->getFullName(),
            'billingProvider' => optional($patient->billingProviderUser())->id,
        ];
        
        return Validator::make(
            $data,
            [
                'conditions'      => [new HasAtLeast2CcmOr1BhiProblems()],
                'phoneNumber'     => 'required|phone:AUTO,US',
                'dob'             => 'required|date',
                'mrn'             => ['required', new HasValidNbiMrn($patient)],
                'name'            => 'required',
                'billingProvider' => 'required|numeric',
            ],
            [
                'phoneNumber.phone' => 'The patient has an invalid phone number.',
            ]
        );
    }
}
