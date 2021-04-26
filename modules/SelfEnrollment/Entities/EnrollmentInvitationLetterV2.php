<?php

namespace CircleLinkHealth\SelfEnrollment\Entities;

use CircleLinkHealth\Core\Entities\BaseModel;
use CircleLinkHealth\Customer\Entities\Practice;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Cache;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\HasMedia;

/**
 * CircleLinkHealth\SelfEnrollment\Entities\EnrollmentInvitationLetterV2
 *
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection|\CircleLinkHealth\Customer\Entities\Media[] $media
 * @property-read int|null $media_count
 * @method static \Illuminate\Database\Eloquent\Builder|EnrollmentInvitationLetterV2 newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EnrollmentInvitationLetterV2 newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|EnrollmentInvitationLetterV2 query()
 * @mixin \Eloquent
 */
class EnrollmentInvitationLetterV2 extends BaseModel implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    protected $table = 'self_enrollment_letters_v2';

    const MEDIA_COLLECTION_LOGO_NAME = 'enrollment_practice_logo';
    const MEDIA_COLLECTION_SIGNATURE_NAME = 'multiple_files';

    const PATIENT_FIRST_NAME = '{first_name}';
    const PATIENT_LAST_NAME = '{last_name}';
    const SIGNATORY_NAME = '{signatory_name}';
    const SIGNATURE = '{signature}';
    const SIGNATORY_SPECIALTY = '{signatory_specialty}';
    const SIGNATORY_TITLE_ATTRIBUTES = '{signatory_title_attributes}';
    const DATE_LETTER_SENT = '{date}';

    protected $fillable = [
        'practice_id',
        'body',
        'options'
    ];

    protected $casts = [
        'is_active'=> 'boolean'
    ];

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('enrollment_letter_practice_logo')->singleFile();
        $this->addMediaCollection('enrollment_letter_signatures');
    }

    public function practice()
    {
        return $this->belongsTo(Practice::class);
    }

    public static function getLetterLogoAndRememberV2(int $practiceId, ?EnrollmentInvitationLetterV2 $letter = null): ?string
    {
        return Cache::remember("letter_logo_for_practice_$practiceId", 5, function () use($letter, $practiceId){
            if (! $letter){
                $letter = self::with('media')
                    ->where('practice_id', $practiceId)
                    ->where('is_active', true)
                    ->first();
            }

            if (! $letter){
                return '';
            }

            return self::getLetterMediaUrl($letter);
        });
    }

    public static function getLetterMediaUrl(?EnrollmentInvitationLetterV2 $letter)
    {
        $logoMedia = optional($letter->getMedia(self::MEDIA_COLLECTION_LOGO_NAME))->first();
        return optional($logoMedia)->getUrl() ?? '';
    }
}
