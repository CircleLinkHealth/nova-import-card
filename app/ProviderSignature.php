<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App;

use Illuminate\Database\Eloquent\Model;

/**
 * App\ProviderSignature.
 *
 * @property int                             $id
 * @property int                             $provider_info_id
 * @property string                          $signature_src
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProviderSignature newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProviderSignature newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\ProviderSignature query()
 * @mixin \Eloquent
 */
class ProviderSignature extends Model
{
    const SIGNATURE_PIC_TYPE = '_signature.png';
    const SIGNATURE_VALUE    = 'depended_on_leader_provider';

    protected $fillable = [
        'provider_info_id',
        'signature_src',
    ];

    protected $table = 'providers_signatures';
}
