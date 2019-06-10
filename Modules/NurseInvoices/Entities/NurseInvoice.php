<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices\Entities;

use App\Contracts\Pdfable;
use App\Services\PdfService;
use App\Traits\NotificationAttachable;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Nurse;
use CircleLinkHealth\NurseInvoices\Traits\Disputable;
use CircleLinkHealth\NurseInvoices\Traits\Nursable;
use Illuminate\Database\Eloquent\Model;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;

class NurseInvoice extends Model implements HasMedia, Pdfable
{
    use Disputable;
    use HasMediaTrait;
    use NotificationAttachable;
    use Nursable;

    protected $casts = [
        'month_year'   => 'date',
        'invoice_data' => ' array',
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

    public function dispute()
    {
        return $this->morphOne(Dispute::class, 'disputable');
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
        return $builder->doesntHave('dispute');
    }

    /**
     * Create a PDF of this resource and return the path to it.
     *
     * @param mixed|null $scale
     *
     * @return string
     */
    public function toPdf($scale = null): string
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
