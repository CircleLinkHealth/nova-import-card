<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices\Entities;

use CircleLinkHealth\Core\Entities\BaseModel;

/**
 * CircleLinkHealth\NurseInvoices\Entities\NurseInvoiceDailyDispute.
 *
 * @property int                                                                                         $id
 * @property int                                                                                         $invoice_id
 * @property string|null                                                                                 $suggested_formatted_time
 * @property string|null                                                                                 $disputed_formatted_time
 * @property \Illuminate\Support\Carbon|null                                                             $disputed_day
 * @property string|null                                                                                 $status
 * @property int                                                                                         $invalidated
 * @property \Illuminate\Support\Carbon|null                                                             $created_at
 * @property \Illuminate\Support\Carbon|null                                                             $updated_at
 * @property \CircleLinkHealth\NurseInvoices\Entities\NurseInvoice                                       $nurseInvoice
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection $revisionHistory
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\NurseInvoices\Entities\NurseInvoiceDailyDispute newModelQuery()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\NurseInvoices\Entities\NurseInvoiceDailyDispute newQuery()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\NurseInvoices\Entities\NurseInvoiceDailyDispute query()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\NurseInvoices\Entities\NurseInvoiceDailyDispute whereCreatedAt($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\NurseInvoices\Entities\NurseInvoiceDailyDispute whereDisputedDay($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\NurseInvoices\Entities\NurseInvoiceDailyDispute whereDisputedFormattedTime($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\NurseInvoices\Entities\NurseInvoiceDailyDispute whereId($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\NurseInvoices\Entities\NurseInvoiceDailyDispute whereInvalidated($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\NurseInvoices\Entities\NurseInvoiceDailyDispute whereInvoiceId($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\NurseInvoices\Entities\NurseInvoiceDailyDispute whereStatus($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\NurseInvoices\Entities\NurseInvoiceDailyDispute whereSuggestedFormattedTime($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\NurseInvoices\Entities\NurseInvoiceDailyDispute whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property int|null $revision_history_count
 */
class NurseInvoiceDailyDispute extends BaseModel
{
    const STATUS_PENDING = 'pending';

    protected $casts = [
        'disputed_day' => 'date',
    ];

    protected $fillable = [
        'invoice_id',
        'suggested_formatted_time',
        'disputed_formatted_time',
        'disputed_day',
        'status',
        'invalidated',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function nurseInvoice()
    {
        return $this->belongsTo(NurseInvoice::class, 'invoice_id', 'id');
    }
}
