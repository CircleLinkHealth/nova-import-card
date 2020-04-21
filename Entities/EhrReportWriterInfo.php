<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Entities;

use Illuminate\Database\Eloquent\Model;
use Storage;

/**
 * CircleLinkHealth\Customer\Entities\EhrReportWriterInfo.
 *
 * @property int                                      $id
 * @property int                                      $user_id
 * @property string|null                              $google_drive_folder_path
 * @property \Illuminate\Support\Carbon|null          $created_at
 * @property \Illuminate\Support\Carbon|null          $updated_at
 * @property \CircleLinkHealth\Customer\Entities\User $user
 * @method static                                   \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\EhrReportWriterInfo newModelQuery()
 * @method static                                   \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\EhrReportWriterInfo newQuery()
 * @method static                                   \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\EhrReportWriterInfo query()
 * @method static                                   \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\EhrReportWriterInfo whereCreatedAt($value)
 * @method static                                   \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\EhrReportWriterInfo whereGoogleDriveFolderPath($value)
 * @method static                                   \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\EhrReportWriterInfo whereId($value)
 * @method static                                   \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\EhrReportWriterInfo whereUpdatedAt($value)
 * @method static                                   \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\EhrReportWriterInfo whereUserId($value)
 * @mixin \Eloquent
 */
class EhrReportWriterInfo extends Model
{
    protected $fillable = [
        'user_id',
        'google_drive_folder_path',
    ];

    protected $table = 'ehr_report_writer_info';

    public function getFolderUrl()
    {
        return Storage::drive('google')->url($this->google_drive_folder_path);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
