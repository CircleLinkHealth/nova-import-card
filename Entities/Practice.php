<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Entities;

use App\CareAmbassadorLog;
use App\ChargeableService;
use App\CLH\Helpers\StringManipulation;
use App\EnrolleeCustomFilter;
use App\Repositories\PatientSummaryEloquentRepository;
use App\Traits\HasChargeableServices;
use App\ValueObjects\PatientReportData;
use Carbon\Carbon;
use CircleLinkHealth\Core\Entities\BaseModel;
use CircleLinkHealth\Customer\Traits\HasSettings;
use CircleLinkHealth\Customer\Traits\SaasAccountable;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use Laravel\Scout\Searchable;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;

/**
 * CircleLinkHealth\Customer\Entities\Practice.
 *
 * @property int $id
 * @property int|null $ehr_id
 * @property int|null $user_id
 * @property string $name
 * @property string|null $display_name
 * @property int $active
 * @property float $clh_pppm
 * @property int $term_days
 * @property string|null $federal_tax_id
 * @property int|null $same_ehr_login
 * @property int|null $same_clinical_contact
 * @property int $auto_approve_careplans
 * @property int $send_alerts
 * @property string|null $weekly_report_recipients
 * @property string $invoice_recipients
 * @property string $bill_to_name
 * @property string|null $external_id
 * @property string $outgoing_phone_number
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property string|null $deleted_at
 * @property string|null $sms_marketing_number
 * @property \App\CarePlanTemplate[]|\Illuminate\Database\Eloquent\Collection $careplan
 * @property \CircleLinkHealth\Customer\Entities\Ehr|null $ehr
 * @property mixed $formatted_name
 * @property mixed $primary_location_id
 * @property mixed $subdomain
 * @property \CircleLinkHealth\Customer\Entities\User|null $lead
 * @property \CircleLinkHealth\Customer\Entities\Location[]|\Illuminate\Database\Eloquent\Collection $locations
 * @property \App\CPRulesPCP[]|\Illuminate\Database\Eloquent\Collection $pcp
 * @property \CircleLinkHealth\Customer\Entities\Settings[]|\Illuminate\Database\Eloquent\Collection $settings
 * @property \CircleLinkHealth\Customer\Entities\User[]|\Illuminate\Database\Eloquent\Collection $users
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Practice active()
 * @method static bool|null forceDelete()
 * @method static \Illuminate\Database\Query\Builder|\App\Practice onlyTrashed()
 * @method static bool|null restore()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Practice whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Practice whereAutoApproveCareplans($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Practice whereBillToName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Practice whereClhPppm($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Practice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Practice whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Practice whereDisplayName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Practice whereEhrId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Practice whereExternalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Practice whereFederalTaxId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Practice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Practice whereInvoiceRecipients($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Practice whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Practice whereOutgoingPhoneNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Practice whereSameClinicalContact($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Practice whereSameEhrLogin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Practice whereSendAlerts($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Practice whereSmsMarketingNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Practice whereTermDays($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Practice whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Practice whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Practice whereWeeklyReportRecipients($value)
 * @method static \Illuminate\Database\Query\Builder|\App\Practice withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\Practice withoutTrashed()
 * @mixin \Eloquent
 *
 * @property int|null $saas_account_id
 * @property \App\CareAmbassadorLog[]|\Illuminate\Database\Eloquent\Collection $careAmbassadorLogs
 * @property \App\ChargeableService[]|\Illuminate\Database\Eloquent\Collection $chargeableServices
 * @property \App\EnrolleeCustomFilter[]|\Illuminate\Database\Eloquent\Collection $enrolleeCustomFilters
 * @property \App\PracticeEnrollmentTips $enrollmentTips
 * @property string $number_with_dashes
 * @property \CircleLinkHealth\Customer\Entities\Media[]|\Illuminate\Database\Eloquent\Collection $media
 * @property \Illuminate\Notifications\DatabaseNotification[]|\Illuminate\Notifications\DatabaseNotificationCollection
 *     $notifications
 * @property \Illuminate\Database\Eloquent\Collection|\Venturecraft\Revisionable\Revision[] $revisionHistory
 * @property \CircleLinkHealth\Customer\Entities\SaasAccount|null $saasAccount
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Practice activeBillable()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Practice
 *     authUserCanAccess($softwareOnly = false)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Practice
 *     authUserCannotAccess()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Practice enrolledPatients()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Practice
 *     hasServiceCode($code)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Practice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Practice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Practice query()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\Practice
 *     whereSaasAccountId($value)
 */
class Practice extends BaseModel implements HasMedia
{
    use HasChargeableServices;
    use HasMediaTrait;
    use HasSettings;
    use Notifiable;
    use SaasAccountable;
    use Searchable;
    use SoftDeletes;

    protected $fillable = [
        'saas_account_id',
        'name',
        'display_name',
        'active',
        'federal_tax_id',
        'user_id',
        'same_clinical_contact',
        'clh_pppm',
        'same_ehr_login',
        'sms_marketing_number',
        'weekly_report_recipients',
        'invoice_recipients',
        'bill_to_name',
        'auto_approve_careplans',
        'send_alerts',
        'outgoing_phone_number',
        'term_days',
    ];

    public function careAmbassadorLogs()
    {
        return $this->belongsToMany(CareAmbassadorLog::class);
    }

    public function careplan()
    {
        return $this->hasMany('App\CarePlanTemplate', 'patient_id');
    }

    public function cpmSettings()
    {
        if ( ! $this->relationLoaded('settings')) {
            $this->load('settings');
        }

        return $this->settings->isEmpty()
            ? $this->syncSettings(new Settings())
            : $this->settings->first();
    }

    public function ehr()
    {
        return $this->belongsTo(Ehr::class);
    }

    public function enrolleeCustomFilters()
    {
        return $this->belongsToMany(
            EnrolleeCustomFilter::class,
            'practice_enrollee_filters',
            'practice_id',
            'filter_id'
        );
    }

    public function enrollmentByProgram(
        Carbon $start,
        Carbon $end
    ) {
        $patients = Patient::whereHas(
            'user',
            function ($q) {
                $q->where('program_id', $this->id);
            }
        )
                           ->whereNotNull('ccm_status')
                           ->get();

        $data = [
            'withdrawn' => 0,
            'paused'    => 0,
            'added'     => 0,
        ];

        foreach ($patients as $patient) {
            if ($patient->created_at > $start->toDateTimeString() && $patient->created_at <= $end->toDateTimeString()) {
                ++$data['added'];
            }

            if ($patient->date_withdrawn > $start->toDateTimeString() && $patient->date_withdrawn <= $end->toDateTimeString()) {
                ++$data['withdrawn'];
            }

            if ($patient->date_paused > $start->toDateTimeString() && $patient->date_paused <= $end->toDateTimeString()) {
                ++$data['paused'];
            }
        }

        return $data;
    }

    /**
     * @return \App\PracticeEnrollmentTips|\Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function enrollmentTips()
    {
        return $this->hasOne('App\PracticeEnrollmentTips', 'practice_id');
    }

    /**
     * @throws \Exception
     *
     * @return array
     */
    public function getAddress()
    {
        $primary = $this->locations()->where('is_primary', 1)->first();

        if (is_null($primary)) {
            $primary = $this->locations()->first();
        }

        if (is_null($primary)) {
            throw new \Exception('This Practice does not have a location.', 500);
        }

        return [
            'line1' => $primary->address_line_1 . ' ' . $primary->address_line_2,
            'line2' => $primary->city . ', ' . $primary->state . ' ' . $primary->postal_code,
        ];
    }

    public function getCountOfUserTypeAtPractice($role)
    {
        $id = $this->id;

        return User
            ::where('user_status', 1)
            ->whereProgramId($this->id)
            ->whereHas(
                'roles',
                function ($q) use (
                    $role
                ) {
                    $q->whereName($role);
                }
            )
            ->count();
    }

    public function getFormattedNameAttribute()
    {
        return ucwords($this->display_name);
    }

    public function getInvoiceData($month, $chargeableServiceId = null)
    {
        if ($chargeableServiceId) {
            //if software only exists on summary we bill for that
            //if service is not software only we count summaries that have this chargeableService and NOT software only.

            $chargeableServiceCode = ChargeableService::findOrFail($chargeableServiceId)->code;

            $isSoftwareOnly = 'Software-Only' == $chargeableServiceCode;

            //for AWV Codes
            if (starts_with($chargeableServiceCode, 'AWV')) {
                $billable = User::ofType('participant')
                                ->ofPractice($this->id)
                                ->whereHas(
                                    'patientAWVSummaries',
                                    function ($query) use ($chargeableServiceCode, $month) {
                                        $query->where('is_billable', true)
                                              ->where('billable_at', '>=',
                                                  $month->copy()->startOfMonth()->startOfDay())
                                              ->where('billable_at', '<=', $month->copy()->endOfMonth()->endOfDay())
                                              ->when($chargeableServiceCode == 'AWV: G0438', function ($query) {
                                                        $query->where('is_initial_visit', 1);
                                                        })
                                              ->when($chargeableServiceCode == 'AWV: G0439', function ($query) {
                                                        $query->where('is_initial_visit', 0);
                                                        });
                                    }
                                )
                                ->count() ?? 0;
            } else {
                $billable = User::ofType('participant')
                                ->ofPractice($this->id)
                                ->whereHas(
                                    'patientSummaries',
                                    function ($query) use ($chargeableServiceId, $isSoftwareOnly, $month) {
                                        $query->whereHas(
                                            'chargeableServices',
                                            function ($query) use ($chargeableServiceId) {
                                                $query->where('id', $chargeableServiceId);
                                            }
                                        )
                                              ->where('month_year', $month->toDateString())
                                              ->where('approved', '=', true)
                                              ->when(
                                                  ! $isSoftwareOnly,
                                                  function ($q) {
                                                      $q->whereDoesntHave(
                                                          'chargeableServices',
                                                          function ($query) {
                                                              $query->where('code', 'Software-Only');
                                                          }
                                                      );
                                                  }
                                              );
                                    }
                                )
                                ->count() ?? 0;
            }
        } else {
            $billable = User::ofType('participant')
                            ->ofPractice($this->id)
                            ->whereHas(
                                'patientSummaries',
                                function ($query) use ($month) {
                                    $query->where('month_year', $month->toDateString())
                                          ->where('approved', '=', true);
                                }
                            )
                            ->count() ?? 0;
        }

        return [
            'clh_address'    => $this->getAddress(),
            'bill_to'        => $this->bill_to_name,
            'practice'       => $this,
            'month'          => $month->format('F, Y'),
            'rate'           => $this->clh_pppm,
            'invoice_num'    => incrementInvoiceNo(),
            'invoice_date'   => Carbon::today()->toDateString(),
            'due_by'         => Carbon::today()->addDays($this->term_days)->toDateString(),
            'invoice_amount' => number_format(round((float)$this->clh_pppm * $billable, 2), 2),
            'billable'       => $billable,
        ];
    }

    public function getInvoiceRecipients()
    {
        return $this->users()->where('send_billing_reports', '=', true)->get();
    }

    public function getInvoiceRecipientsArray()
    {
        return array_values(array_filter(array_map('trim', explode(',', $this->invoice_recipients))));
    }

    public function getItemizedPatientData($month)
    {
        $repo = app(PatientSummaryEloquentRepository::class);

        $data          = [];
        $data['name']  = $this->display_name;
        $data['month'] = $month->toDateString();

        $patients = User::orderBy('first_name', 'asc')
                        ->ofType('participant')
                        ->with(
                            [
                                'patientSummaries' => function ($q) use ($month) {
                                    $q
                                        ->with(['billableBhiProblems'])
                                        ->where('month_year', $month->toDateString())
                                        ->where('approved', '=', true);
                                },
                                'billingProvider',
                            ]
                        )
                        ->whereProgramId($this->id)
                        ->whereHas(
                            'patientSummaries',
                            function ($query) use ($month) {
                                $query->where('month_year', $month->toDateString())
                                      ->where('approved', '=', true);
                            }
                        )
                        ->chunk(
                            500,
                            function ($patients) use (&$data, $repo, $month) {
                                foreach ($patients as $u) {
                                    $summary = $u->patientSummaries->first();

                                    if ( ! $repo->hasBillableProblemsNameAndCode($summary)) {
                                        $summary = $repo->fillBillableProblemsNameAndCode($summary);
                                        $summary->save();
                                    }

                                    $patientData = new PatientReportData();
                                    $patientData->setCcmTime(round($summary->ccm_time / 60, 2));
                                    $patientData->setBhiTime(round($summary->bhi_time / 60, 2));
                                    $patientData->setName($u->getFullName());
                                    $patientData->setDob($u->getBirthDate());
                                    $patientData->setPractice($u->program_id);
                                    $patientData->setProvider($u->getBillingProviderName());
                                    $patientData->setBillingCodes($u->billingCodes($month));

                                    $patientData->setProblem1Code($summary->billable_problem1_code);
                                    $patientData->setProblem1($summary->billable_problem1);

                                    $patientData->setProblem2Code($summary->billable_problem2_code);
                                    $patientData->setProblem2($summary->billable_problem2);

                                    $patientData->setBhiCode(
                                        optional(optional($summary->billableProblems->first())->pivot)->icd_10_code
                                    );
                                    $patientData->setBhiProblem(
                                        optional(optional($summary->billableProblems->first())->pivot)->name
                                    );

                                    $data['patientData'][$u->id] = $patientData;
                                }
                            }
                        );

        $data['patientData'] = array_key_exists('patientData', $data)
            ? collect($data['patientData'])->sortBy(
                function ($data) {
                    return sprintf('%-12s%s', $data->getProvider(), $data->getName());
                }
            )
            : null;

        $awvPatients = User::ofType('participant')
                           ->ofPractice($this->id)
                           ->whereHas(
                               'patientAWVSummaries',
                               function ($query) use ($month) {
                                   $query->where('is_billable', true)
                                         ->where('month_year', $month->toDateString());
                               }
                           )
                           ->with(
                               [
                                   'patientAWVSummaries' => function ($q) use ($month) {
                                       $q->where('is_billable', true)
                                         ->where('month_year', $month->toDateString());
                                   },
                                   'billingProvider',
                               ]
                           )
                           ->chunk(
                               100,
                               function ($patients) use (&$data) {
                                   foreach ($patients as $u) {
                                       $summary = $u->patientAWVSummaries->first();

                                       $patientData = new PatientReportData();
                                       $patientData->setName($u->getFullName());
                                       $patientData->setDob($u->getBirthDate());
                                       $patientData->setPractice($u->program_id);
                                       $patientData->setProvider($u->getBillingProviderName());
                                       $patientData->setAwvDate($summary->completed_at);

                                       $data['awvPatientData'][$u->id] = $patientData;
                                   }
                               }
                           );

        $data['awvPatientData'] = array_key_exists('awvPatientData', $data)
            ? collect($data['awvPatientData'])->sortBy(
                function ($data) {
                    return sprintf('%-12s%s', $data->getProvider(), $data->getName());
                }
            )
            : null;

        return $data;
    }

    /**
     * Get phone number in this format xxx-xxx-xxxx.
     *
     * @return string
     */
    public function getNumberWithDashesAttribute()
    {
        return (new StringManipulation())->formatPhoneNumber($this->outgoing_phone_number);
    }

    public function getPrimaryLocationIdAttribute()
    {
        $loc = $this->locations->where('is_primary', '=', true)->first();

        return $loc
            ? $loc->id
            : null;
    }

    public static function getProviders($practiceId)
    {
        $providers = User::whereHas(
            'practices',
            function ($q) use (
                $practiceId
            ) {
                $q->where('id', '=', $practiceId);
            }
        )->whereHas(
            'roles',
            function ($q) {
                $q->where('name', '=', 'provider');
            }
        )->get();

        return $providers;
    }

    /**
     * Get the value used to index the model.
     *
     * @return mixed
     */
    public function getScoutKey()
    {
        return $this->id;
    }

    public function getSubdomainAttribute()
    {
        return explode('.', $this->domain)[0];
    }

    public function getWeeklyReportRecipientsArray()
    {
        return array_map('trim', explode(',', $this->weekly_report_recipients));
    }

    public function isTwilioEnabled()
    {
        $settings = $this->cpmSettings();

        return boolval($settings->twilio_enabled);
    }

    public function lead()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function locationId()
    {
        return $this->location_id;
    }

    public function locations()
    {
        return $this->hasMany(Location::class);
    }

    public function nurses()
    {
        return $this->users()->whereHas(
            'roles',
            function ($q) {
                $q->where('name', '=', 'care-center')->orWhere('name', 'registered-nurse');
            }
        );
    }

    public function patients()
    {
        return $this->users()->ofType('participant')->whereHas('patientInfo');
    }

    public function pcp()
    {
        return $this->hasMany('App\CPRulesPCP', 'prov_id', 'id');
    }

    public function primaryLocation()
    {
        return $this->locations->where('is_primary', '=', true)->first();
    }

    public function providers()
    {
        return Practice::getProviders($this->id);
    }

    public function scopeActive($q)
    {
        return $q->whereActive(1);
    }

    public function scopeActiveBillable($q)
    {
        if ( ! isProductionEnv()) {
            return $q->whereActive(1);
        }

        return $q->whereActive(1)
                 ->whereNotIn('name', ['demo', 'testdrive', 'mdally-demo']);
    }

    public function scopeAuthUserCanAccess($q, $softwareOnly = false)
    {
        $user = auth()->user();
        if ($softwareOnly) {
            $roleIds               = Role::getIdsFromNames(['software-only']);
            $softwareOnlyPractices = $user->practices(true, true, $roleIds)->pluck('id')->all();

            return $q->whereIn('id', $softwareOnlyPractices);
        }

        return $q->whereIn('id', $user->practices->pluck('id')->all());
    }

    public function scopeAuthUserCannotAccess($q)
    {
        return $q->whereNotIn('id', auth()->user()->practices->pluck('id')->all());
    }

    public function scopeEnrolledPatients($builder)
    {
        return $builder->with(
            [
                'patients' => function ($q) {
                    $q->with(
                        [
                            'patientInfo',
                            'activities',
                        ]
                    )
                      ->whereHas(
                          'patientInfo',
                          function ($patient) {
                              $patient->where('ccm_status', Patient::ENROLLED);
                          }
                      );
                },
            ]
        );
    }

    /**
     * Get Scout index name for the model.
     *
     * @return string
     */
    public function searchableAs()
    {
        return 'practices_index';
    }

    public function setDirectMailCareplanApprovalReminders($bool)
    {
        $settings = $this->cpmSettings();

        $settings->dm_careplan_approval_reminders = $bool;
        $settings->save();
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray()
    {
        return [
            'name'         => $this->name,
            'display_name' => $this->display_name,
        ];
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'practice_role_user', 'program_id', 'user_id')
                    ->withPivot('role_id', 'send_billing_reports')
                    ->withTimestamps();
    }
}
