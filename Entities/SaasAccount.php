<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Customer\Entities;

use CircleLinkHealth\Core\Entities\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia\HasMedia;
use Spatie\MediaLibrary\HasMedia\HasMediaTrait;

/**
 * CircleLinkHealth\Customer\Entities\SaasAccount.
 *
 * @property int                                                                                         $id
 * @property string                                                                                      $name
 * @property string                                                                                      $slug
 * @property string|null                                                                                 $logo_path
 * @property \Illuminate\Support\Carbon|null                                                             $created_at
 * @property \Illuminate\Support\Carbon|null                                                             $updated_at
 * @property string|null                                                                                 $deleted_at
 * @property \CircleLinkHealth\Customer\Entities\Media[]|\Illuminate\Database\Eloquent\Collection        $media
 * @property \CircleLinkHealth\Customer\Entities\Practice[]|\Illuminate\Database\Eloquent\Collection     $practices
 * @property \CircleLinkHealth\Revisionable\Entities\Revision[]|\Illuminate\Database\Eloquent\Collection $revisionHistory
 * @property \CircleLinkHealth\Customer\Entities\User[]|\Illuminate\Database\Eloquent\Collection         $users
 * @method   static                                                                                      bool|null forceDelete()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\SaasAccount newModelQuery()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\SaasAccount newQuery()
 * @method   static                                                                                      \Illuminate\Database\Query\Builder|\CircleLinkHealth\Customer\Entities\SaasAccount onlyTrashed()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\SaasAccount query()
 * @method   static                                                                                      bool|null restore()
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\SaasAccount whereCreatedAt($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\SaasAccount whereDeletedAt($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\SaasAccount whereId($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\SaasAccount whereLogoPath($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\SaasAccount whereName($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\SaasAccount whereSlug($value)
 * @method   static                                                                                      \Illuminate\Database\Eloquent\Builder|\CircleLinkHealth\Customer\Entities\SaasAccount whereUpdatedAt($value)
 * @method   static                                                                                      \Illuminate\Database\Query\Builder|\CircleLinkHealth\Customer\Entities\SaasAccount withTrashed()
 * @method   static                                                                                      \Illuminate\Database\Query\Builder|\CircleLinkHealth\Customer\Entities\SaasAccount withoutTrashed()
 * @mixin \Eloquent
 * @property int|null $media_count
 * @property int|null $practices_count
 * @property int|null $revision_history_count
 * @property int|null $users_count
 */
class SaasAccount extends BaseModel implements HasMedia
{
    use HasMediaTrait;
    use
        SoftDeletes;

    protected $fillable = [
        'name',
        'logo_path',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function practices()
    {
        return $this->hasMany(Practice::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }
    
    public function isCircleLinkHealth() {
        return 'circlelink-health' == $this->slug;
    }
}
