<?php

namespace CircleLinkHealth\SelfEnrollment\Tests\Feature;

use CircleLinkHealth\Customer\Tests\CustomerTestCase;
use CircleLinkHealth\Customer\Traits\UserHelpers;
use CircleLinkHealth\SelfEnrollment\Entities\EnrollmentInvitationLetterV2;
use CircleLinkHealth\SelfEnrollment\Http\Controllers\SelfEnrollmentController;
use CircleLinkHealth\SelfEnrollment\Services\SelfEnrollmentLetterService;
use CircleLinkHealth\SelfEnrollment\ValueObjects\PracticeLetterData;
use CircleLinkHealth\SharedModels\Entities\Enrollee;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;


class DiyLettersTest extends CustomerTestCase
{
    use UserHelpers;
    use SelfEnrollmentTestHelpers;
    use WithFaker;

    public Enrollee $enrollee;
    public $letter;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('media');
        /** @var Enrollee $enrollee */
        $this->enrollee = $this->createEnrollees(1);
        /** @var EnrollmentInvitationLetterV2 $letter */
        $this->letter = $this->createLetter($this->enrollee->practice_id);
    }

    public function createLetter(int $practiceId)
    {
        return EnrollmentInvitationLetterV2::firstOrCreate([
            'practice_id' => $practiceId,
            'body' => "This is a body",
            'options'=> json_encode([
                'logo_size' => '60px',
                'logo_position' => 'center',
                'logo_distance_from_text' => '10px'
            ]),
            'is_active' => true
        ]);
    }

    public function test_if_parent_signatory_is_also_child_signatory_it_will_render_signature()
    {
        $childSignatoryProviderId = $this->createUser($this->enrollee->practice_id, 'provider')->id;
        $parentSignatoryProviderId = $this->enrollee->provider_id;
        /** @var UploadedFile $uploadedFile */
        $uploadedFile = UploadedFile::fake()->image('image-1.png');
        $signature = $this->letter->addMedia($uploadedFile->getRealPath())
            ->withCustomProperties(['provider_signature_id' => $parentSignatoryProviderId,
                'providers_under_same_signature' => [$parentSignatoryProviderId, $childSignatoryProviderId],
                'signatory_title_attributes' => null
            ])
            ->toMediaCollection($uploadedFile->getClientOriginalName(), 'media');

        $signature->update([
            'collection_name' => EnrollmentInvitationLetterV2::MEDIA_COLLECTION_SIGNATURE_NAME,
            'name' => "{$this->enrollee->practice->name}_signature",
            'file_name'=> "{$this->enrollee->practice_id}-signature.png",
            'mime_type'=> $uploadedFile->getClientMimeType(),
            'disk'=> 'media'
        ]);

        $letterService = app(SelfEnrollmentLetterService::class);
        $letterForView = $letterService->createLetterToRender($this->enrollee->user, $this->letter, now()->toDateString());

        $signaturesFromLetter = $letterForView->signatures();
        self::assertTrue(in_array($this->enrollee->provider_id, $signaturesFromLetter->first()['providers_under_same_signature']));
        self::assertTrue($signaturesFromLetter->first()['provider_id'] ===  $parentSignatoryProviderId);
    }

    public function test_it_will_show_the_correct_provider_signature_on_letter_depending_on_enrollee_provider_id()
    {
        $mainSignatoryProvider = $this->createUser($this->enrollee->practice_id, 'provider');
        /** @var UploadedFile $uploadedFile */
        $uploadedFile = UploadedFile::fake()->image('image-1.png');
        $signature = $this->letter->addMedia($uploadedFile->getRealPath())
            ->withCustomProperties(['provider_signature_id' => $mainSignatoryProvider->id,
                'providers_under_same_signature' => [$this->enrollee->provider_id],
                'signatory_title_attributes' => null
            ])
            ->toMediaCollection($uploadedFile->getClientOriginalName(), 'media');

        $signature->update([
            'collection_name' => EnrollmentInvitationLetterV2::MEDIA_COLLECTION_SIGNATURE_NAME,
            'name' => "{$this->enrollee->practice->name}_signature",
            'file_name'=> "{$this->enrollee->practice_id}-signature.png",
            'mime_type'=> $uploadedFile->getClientMimeType(),
            'disk'=> 'media'
        ]);

        $letterService = app(SelfEnrollmentLetterService::class);
        $letterForView = $letterService->createLetterToRender($this->enrollee->user, $this->letter, now()->toDateString());

        $signaturesFromLetter = $letterForView->signatures();
        self::assertTrue(in_array($this->enrollee->provider_id, $signaturesFromLetter->first()['providers_under_same_signature']));
        self::assertTrue($signaturesFromLetter->first()['provider_id'] ===  $mainSignatoryProvider->id);
    }

    public function test_it_will_fetch_letter_with_logo_from_media()
    {
        /** @var UploadedFile $uploadedFile */
        $uploadedFile = UploadedFile::fake()->image('logo_image-1.png');

        $logo = $this->letter->addMedia($uploadedFile->getRealPath())->toMediaCollection($uploadedFile->getClientOriginalName(), 'media');

        $logo->update([
            'collection_name' => EnrollmentInvitationLetterV2::MEDIA_COLLECTION_LOGO_NAME,
            'name' => "{$this->enrollee->practice->name}_logo",
            'file_name'=> "{$this->enrollee->practice_id}-logo.png",
            'mime_type'=> $uploadedFile->getClientMimeType(),
            'disk'=> 'media'
        ]);

        $letterService = app(SelfEnrollmentLetterService::class);

        /** @var PracticeLetterData $letterForView */
        $letterForView = $letterService->createLetterToRender($this->enrollee->user, $this->letter, now()->toDateString());

        self::assertNotEmpty($letterForView->logoUrl());
        self::assertTrue($letterForView->logoUrl() === $logo->getUrl());

        $view = $this->view('selfEnrollment::enrollment-letterV2',[
            'letter' => $letterForView,
            'hideButtons' => false,
            'userEnrolleeId' => $this->enrollee->user_id,
            'isSurveyOnlyUser' => $this->enrollee->user->isSurveyOnly(),
            'buttonColor'=>SelfEnrollmentController::DEFAULT_BUTTON_COLOR,
            'practiceName' => $this->enrollee->practice->display_name,
            'disableButtons' => false
        ]);

        $view->assertSee($letterForView->logoUrl());
        $view->assertSee($letterForView->body());

    }

    public function test_an_admin_can_review_the_diy_letter()
    {
        $letterService = app(SelfEnrollmentLetterService::class);
        /** @var PracticeLetterData $letterToRender */
        $letterToRender =  $letterService->createLetterToRender($this->enrollee->user, $this->letter, now()->toDateString());

        self::assertDatabaseHas('self_enrollment_letters_v2',[
            'id' => $this->letter->id,
        ]);

        $response = $this->get(route('self.enrollment.letter.review',[
            'practiceId'=> $this->enrollee->practice_id,
            'userId'=> $this->enrollee->user_id,
        ]));

        $response->assertStatus(200);

        $view = $this->view('selfEnrollment::enrollment-letterV2',[
            'letter' => $letterToRender,
            'hideButtons' => false,
            'userEnrolleeId' => $this->enrollee->user_id,
            'isSurveyOnlyUser' => true,
            'buttonColor'=>SelfEnrollmentController::DEFAULT_BUTTON_COLOR,
            'dateLetterSent' => now()->toDateString(),
            'practiceName' => $this->enrollee->practice->display_name,
            'disableButtons'=>true
        ]);
        $view->assertSee($letterToRender->logoUrl());
        $view->assertSee($letterToRender->body());

    }
}
