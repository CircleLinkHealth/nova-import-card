<?php namespace App\Models\CCD;

use App\CLH\CCD\ItemLogger\ModelLogRelationship;
use Illuminate\Database\Eloquent\Model;
use Prettus\Repository\Contracts\Transformable;
use Prettus\Repository\Traits\TransformableTrait;

class Ccda extends Model implements Transformable
{

    use ModelLogRelationship, TransformableTrait;

    //define sources here
    const ATHENA_API = 'athena_api';
    const API = 'api';
    const EMR_DIRECT = 'emr_direct';
    const IMPORTER = 'importer';

    const EMAIL_DOMAIN_TO_VENDOR_MAP = [
        //Carolina Medical Associates
        '@direct.novanthealth.org'        => 10,
        '@test.directproject.net'         => 14,
        '@direct.welltrackone.com'        => 14,
        '@treatrelease.direct.aprima.com' => 1,
    ];

    protected $fillable = [
        'user_id',
        'patient_id',
        'vendor_id',
        'source',
        'imported',
        'xml',
        'json',
    ];


    public function qaSummary()
    {
        return $this->hasOne(QAImportSummary::class);
    }
}
