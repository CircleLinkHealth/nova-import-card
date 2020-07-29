<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices\Entities;

use App\Contracts\Pdfable;
use App\Traits\NotificationAttachable;
use Carbon\Carbon;
use CircleLinkHealth\Core\Entities\BaseModel;
use CircleLinkHealth\Core\Services\PdfService;
use CircleLinkHealth\Customer\Entities\Nurse;
use CircleLinkHealth\NurseInvoices\Traits\Disputable;
use CircleLinkHealth\NurseInvoices\Traits\Nursable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;

/**
 * CircleLinkHealth\NurseInvoices\Entities\NurseInvoice.
 *
 * @property int                                                                                                             $id
 * @property int                                                                                                             $nurse_info_id
 * @property \Illuminate\Support\Carbon                                                                                      $month_year
 * @property int|null                                                                                                        $is_nurse_approved
 * @property \Illuminate\Support\Carbon|null                                                                                 $nurse_approved_at
 * @property \Illuminate\Support\Carbon|null                                                                                 $sent_to_accountant_at
 * @property mixed                                                                                                           $invoice_data
 * @property \Illuminate\Support\Carbon|null                                                                                 $created_at
 * @property \Illuminate\Support\Carbon|null                                                                                 $updated_at
 * @property \CircleLinkHealth\NurseInvoices\Entities\NurseInvoiceDailyDispute[]|\Illuminate\Database\Eloquent\Collection    $dailyDisputes
 * @property \CircleLinkHealth\NurseInvoices\Entities\Dispute                                                                $dispute
 * @property \CircleLinkHealth\Customer\Entities\Media[]|\Illuminate\Database\Eloquent\Collection                            $media
 * @property \CircleLinkHealth\Core\Entities\DatabaseNotification[]|\Illuminate\Notifications\DatabaseNotificationCollection $notifications
 * @property \CircleLinkHealth\Customer\Entities\Nurse                                                                       $nurse
 * @property \CircleLinkHealth\Customer\Entities\Nurse                                                                       $nurseInfo
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\NurseInvoices\Entities\NurseInvoice approved()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\NurseInvoices\Entities\NurseInvoice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\NurseInvoices\Entities\NurseInvoice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\NurseInvoices\Entities\NurseInvoice notApproved()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\NurseInvoices\Entities\NurseInvoice ofNurses($userId)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\NurseInvoices\Entities\NurseInvoice query()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\NurseInvoices\Entities\NurseInvoice undisputed()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\NurseInvoices\Entities\NurseInvoice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\NurseInvoices\Entities\NurseInvoice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\NurseInvoices\Entities\NurseInvoice whereInvoiceData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\NurseInvoices\Entities\NurseInvoice whereIsNurseApproved($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\NurseInvoices\Entities\NurseInvoice whereMonthYear($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\NurseInvoices\Entities\NurseInvoice whereNurseApprovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\NurseInvoices\Entities\NurseInvoice whereNurseInfoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\NurseInvoices\Entities\NurseInvoice whereSentToAccountantAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\NurseInvoices\Entities\NurseInvoice whereUpdatedAt($value)
 * @mixin \Eloquent
 *
 * @property int|null                                                                                    $daily_disputes_count
 * @property int|null                                                                                    $media_count
 * @property int|null                                                                                    $notifications_count
 * @property \CircleLinkHealth\NurseInvoices\Entities\Dispute[]|\Illuminate\Database\Eloquent\Collection $disputes
 * @property int|null                                                                                    $disputes_count
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection $revisionHistory
 * @property int|null                                                                                    $revision_history_count
 */
class NurseInvoice extends BaseModel implements HasMedia, Pdfable
{
    use Disputable;
    use HasMediaTrait;
    use NotificationAttachable;
    use Nursable;
    const CSV_DOWNLOAD_FORMAT = 'csv';

    const PDF_DOWNLOAD_FORMAT = 'pdf';

    protected $casts = [
        'month_year'   => 'date',
        'invoice_data' => 'array',
    ];

    protected $dates = [
        'month_year',
        'nurse_approved_at',
        'sent_to_accountant_at',
    ];

    protected $fillable = [
        'nurse_info_id',
        'month_year',
        'sent_to_accountant_at',
        'invoice_data',
        'approval_date',
        'is_nurse_approved',
        'nurse_approved_at',
    ];

    /**
     * @return HasMany
     */
    public function dailyDisputes()
    {
        return $this->hasMany(NurseInvoiceDailyDispute::class, 'invoice_id', 'id');
    }

    public function disputes()
    {
        return $this->morphMany(Dispute::class, 'disputable');
    }

    public function nurse()
    {
        return $this->belongsTo(Nurse::class, 'nurse_info_id');
    }

    public function scopeApproved($builder)
    {
        return $builder->where('is_nurse_approved', true);
    }

    public function scopeNotApproved($builder)
    {
        return $builder->whereNull('is_nurse_approved');
    }

    public function scopeUndisputed($builder)
    {
        return $builder->doesntHave('disputes');
    }

    /**
     * Create a PDF of this resource and return the path to it.
     *
     * @param mixed|null $scale
     */
    public function toPdf($scale = null): ?string
    {
        $invoiceData = $this->invoice_data ?? [];

        if ( ! $invoiceData) {
            return null;
        }

        $name = trim($invoiceData['nurseFullName']).'-'.Carbon::now()->toDateString();
        $link = $name.'.pdf';

        return app(PdfService::class)->createPdfFromView(
            'nurseinvoices::invoice-v3',
            $invoiceData,
            storage_path("download/${name}.pdf"),
            [
                'margin-top'    => '6',
                'margin-left'   => '6',
                'margin-bottom' => '6',
                'margin-right'  => '6',
                'footer-right'  => 'Page [page] of [toPage]',
                'footer-left'   => 'report generated on '.Carbon::now()->format('m-d-Y').' at '.Carbon::now()->format(
                    'H:iA'
                ),
                'footer-font-size' => '6',
            ]
        );
    }

    /**
     * @return \Spatie\MediaLibrary\Models\Media
     */
    public function toPdfAndStoreAsMedia()
    {
        return $this->addMedia($this->toPdf())->toMediaCollection(
            "monthly_invoice_{$this->month_year->year}_{$this->month_year->month}"
        );
    }
}
