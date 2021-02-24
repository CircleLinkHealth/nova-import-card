<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Entities;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\Models\Media as SpatieMedia;

/**
 * CircleLinkHealth\Customer\Entities\Media.
 *
 * @property int                                                                                  $id
 * @property int                                                                                  $model_id
 * @property string                                                                               $model_type
 * @property string                                                                               $collection_name
 * @property string                                                                               $name
 * @property string                                                                               $file_name
 * @property string|null                                                                          $mime_type
 * @property string                                                                               $disk
 * @property int                                                                                  $size
 * @property array                                                                                $manipulations
 * @property array                                                                                $custom_properties
 * @property array                                                                                $responsive_images
 * @property int|null                                                                             $order_column
 * @property \Illuminate\Support\Carbon|null                                                      $created_at
 * @property \Illuminate\Support\Carbon|null                                                      $updated_at
 * @property mixed                                                                                $extension
 * @property mixed                                                                                $human_readable_size
 * @property mixed                                                                                $type
 * @property \CircleLinkHealth\Customer\Entities\Media[]|\Illuminate\Database\Eloquent\Collection $model
 * @method static                                                                               \Illuminate\Database\Eloquent\Builder|\App\Media newModelQuery()
 * @method static                                                                               \Illuminate\Database\Eloquent\Builder|\App\Media newQuery()
 * @method static                                                                               \Illuminate\Database\Eloquent\Builder|SpatieMedia ordered()
 * @method static                                                                               \Illuminate\Database\Eloquent\Builder|\App\Media query()
 * @method static                                                                               \Illuminate\Database\Eloquent\Builder|\App\Media whereCollectionName($value)
 * @method static                                                                               \Illuminate\Database\Eloquent\Builder|\App\Media whereCreatedAt($value)
 * @method static                                                                               \Illuminate\Database\Eloquent\Builder|\App\Media whereCustomProperties($value)
 * @method static                                                                               \Illuminate\Database\Eloquent\Builder|\App\Media whereDisk($value)
 * @method static                                                                               \Illuminate\Database\Eloquent\Builder|\App\Media whereFileName($value)
 * @method static                                                                               \Illuminate\Database\Eloquent\Builder|\App\Media whereId($value)
 * @method static                                                                               \Illuminate\Database\Eloquent\Builder|\App\Media whereManipulations($value)
 * @method static                                                                               \Illuminate\Database\Eloquent\Builder|\App\Media whereMimeType($value)
 * @method static                                                                               \Illuminate\Database\Eloquent\Builder|\App\Media whereModelId($value)
 * @method static                                                                               \Illuminate\Database\Eloquent\Builder|\App\Media whereModelType($value)
 * @method static                                                                               \Illuminate\Database\Eloquent\Builder|\App\Media whereName($value)
 * @method static                                                                               \Illuminate\Database\Eloquent\Builder|\App\Media whereOrderColumn($value)
 * @method static                                                                               \Illuminate\Database\Eloquent\Builder|\App\Media whereResponsiveImages($value)
 * @method static                                                                               \Illuminate\Database\Eloquent\Builder|\App\Media whereSize($value)
 * @method static                                                                               \Illuminate\Database\Eloquent\Builder|\App\Media whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property int|null                        $is_pdf
 * @property int|null                        $is_ccda
 * @property int|null                        $is_upg0506
 * @property int|null                        $is_upg0506_complete
 * @property string|null                     $mrn
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @method static                          \Illuminate\Database\Query\Builder|\CircleLinkHealth\Customer\Entities\Media onlyTrashed()
 * @method static                          \Illuminate\Database\Query\Builder|\CircleLinkHealth\Customer\Entities\Media withTrashed()
 * @method static                          \Illuminate\Database\Query\Builder|\CircleLinkHealth\Customer\Entities\Media withoutTrashed()
 */
class Media extends SpatieMedia
{
    use SoftDeletes;

    protected $table = 'media';

    /**
     * Get the file.
     *
     * @return string
     */
    public function downloadFile()
    {
        return Storage::disk($this->disk)->download("{$this->id}/{$this->file_name}");
    }

    /**
     * Get the file.
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     *
     * @return string
     */
    public function getFile()
    {
        return Storage::disk($this->disk)->get($this->getPath());
    }
}
