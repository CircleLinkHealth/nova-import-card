<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices\Entities;

use CircleLinkHealth\Core\Entities\BaseModel;
use CircleLinkHealth\Customer\Entities\Nurse;
use CircleLinkHealth\Customer\Entities\User;

/**
 * CircleLinkHealth\NurseInvoices\Entities\NurseInvoiceExtra.
 *
 * @property int                                                                                         $id
 * @property int                                                                                         $user_id
 * @property \Illuminate\Support\Carbon|null                                                             $date
 * @property string|null                                                                                 $unit
 * @property int|null                                                                                    $value
 * @property \Illuminate\Support\Carbon|null                                                             $created_at
 * @property \Illuminate\Support\Carbon|null                                                             $updated_at
 * @property \CircleLinkHealth\Customer\Entities\Nurse                                                   $nurse
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection $revisionHistory
 * @property \CircleLinkHealth\Customer\Entities\User                                                    $user
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\NurseInvoices\Entities\NurseInvoiceExtra newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\NurseInvoices\Entities\NurseInvoiceExtra newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\NurseInvoices\Entities\NurseInvoiceExtra query()
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\NurseInvoices\Entities\NurseInvoiceExtra whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\NurseInvoices\Entities\NurseInvoiceExtra whereDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\NurseInvoices\Entities\NurseInvoiceExtra whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\NurseInvoices\Entities\NurseInvoiceExtra whereUnit($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\NurseInvoices\Entities\NurseInvoiceExtra whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\NurseInvoices\Entities\NurseInvoiceExtra whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\NurseInvoices\Entities\NurseInvoiceExtra whereValue($value)
 * @mixin \Eloquent
 *
 * @property int|null $revision_history_count
 */
class NurseInvoiceExtra extends BaseModel
{
    protected $casts = [
        'date' => 'date',
    ];
    protected $fillable = ['user_id', 'date', 'unit', 'value'];
    protected $table    = 'nurse_invoice_extras';

    public function nurse()
    {
        return $this->belongsTo(Nurse::class, 'user_id', 'user_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
