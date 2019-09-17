<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Notifications\Channels\DirectMailChannel;
use App\Notifications\Channels\FaxChannel;
use App\Notifications\SendCareDocument;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Location;
use CircleLinkHealth\Customer\Entities\Media;
use CircleLinkHealth\Customer\Entities\PatientAWVSurveyInstanceStatus;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PatientCareDocumentsController extends Controller
{
    /**
     * The size of ten megabytes, used for file validation.
     */
    const TEN_MB = 10485760;

    /*
     * Available reports that User can see as view-generated in AWV as of yet.
     *
     * @var array
     * */
    private $availableReportsForSending = [
        'PPP',
        'Provider Report',
    ];

    public function downloadCareDocument($id, $mediaId)
    {
        $mediaItem = $this->getMediaItemById($id, $mediaId);

        if ( ! $mediaItem) {
            throw new \Exception('Media for Patient does not exist.', 500);
        }

        return $this->downloadMedia($mediaItem);
    }

    public function getCareDocuments(Request $request, $patientId, $showPast = false)
    {
        $patientAWVStatuses = PatientAWVSurveyInstanceStatus::where('patient_id', $patientId)
            ->when( ! $showPast, function ($query) {
                $query->where('year', Carbon::now()->year);
            })
            ->get();

        $files = Media::where('collection_name', 'patient-care-documents')
            ->where('model_id', $patientId)
            ->whereIn('model_type', ['App\User', 'CircleLinkHealth\Customer\Entities\User'])
            ->get()
            ->sortByDesc('created_at')
            ->mapToGroups(function ($item, $key) {
                $docType = $item->getCustomProperty('doc_type');

                return [$docType => $item];
            })
            ->reject(function ($value, $key) {
                return ! $key;
            })
            //get the latest file from each category
            ->unless('true' == $showPast, function ($files) {
                return $files->map(function ($typeGroup) {
                    return collect([$typeGroup->first()]);
                });
            });

        return response()->json([
            'files'              => $files->toArray(),
            'patientAWVStatuses' => $patientAWVStatuses->toArray(),
        ]);
    }

    public function sendCareDocument($patientId, $mediaId, $channelInput, $addressOrFax)
    {
        $media = $this->getMediaItemById($patientId, $mediaId);

        if ( ! $media) {
            return response()->json(
                'Something went wrong. Media not found.',
                400
            );
        }

        if ( ! in_array($media->getCustomProperty('doc_type'), $this->availableReportsForSending)) {
            return response()->json(
                'This has not yet been implemented.',
                400
            );
        }

        $patient = User::find($patientId);

        if ( ! $patient) {
            return response()->json(
                'Something went wrong. Patient not found.',
                400
            );
        }

        $channel = $this->setChannelAndValidateInput($channelInput, $addressOrFax);

        if (is_a($channel, Validator::class)) {
            return response()->json($channel->messages(), 400);
        }

        $notifiable = $this->getNotifiableEntity($channelInput, $addressOrFax);

        if ( ! $notifiable) {
            return response()->json(
                'Could not find Recipient in our system.',
                400
            );
        }

        $notifiable->notify(new SendCareDocument($media, $patient, $channel));

        return response()->json(
            'Document sent successfully!',
            200
        );
    }

    public function uploadCareDocuments(Request $request, $patientId)
    {
        $patient = User::findOrFail($patientId);

        foreach ($request->file()['file'] as $file) {
            if ('application/pdf' !== $file->getMimeType()) {
                return response()->json(
                    'The file you are trying to upload is not a PDF.',
                    400
                );
            }
            //if file is larger than 10MB
            if ($file->getSize() > $this::TEN_MB) {
                return response()->json(
                    'The file you are trying to upload is too large.',
                    400
                );
            }
            $patient->addMedia($file)
                ->withCustomProperties(['doc_type' => $request->doc_type])
                ->toMediaCollection('patient-care-documents');
        }

        return response()->json([]);
    }

    public function viewCareDocument($id, $mediaId)
    {
        $mediaItem = $this->getMediaItemById($id, $mediaId);

        if ( ! $mediaItem) {
            throw new \Exception('Media for Patient does not exist.', 500);
        }

        return response($mediaItem->getFile(), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$mediaItem->name.'"',
        ]);
    }

    private function getMediaItemById($modelId, $mediaId)
    {
        return Media::where('collection_name', 'patient-care-documents')
            ->where('model_id', $modelId)
            ->whereIn('model_type', ['App\User', 'CircleLinkHealth\Customer\Entities\User'])
            ->find($mediaId);
    }

    private function getNotifiableEntity($channel, $input)
    {
        switch ($channel) {
            case 'email':
                $notifiable = User::whereEmail($input)->first();
                break;
            case 'direct':
                $notifiable = User::whereHas('emrDirect', function ($emr) use ($input) {
                    $emr->where('address', $input);
                })->first();
                break;
            case 'fax':
                $notifiable = Location::whereFax($input)->first();
                break;
            default:
                $notifiable = null;
        }

        return $notifiable;
    }

    private function setChannelAndValidateInput($channel, $input)
    {
        switch ($channel) {
            case 'email':
                $channel         = ['mail'];
                $validationRules = ['required', 'email'];
                break;
            case 'direct':
                $channel         = [DirectMailChannel::class];
                $validationRules = ['required', 'email'];
                break;
            case 'efax':
                $channel         = [FaxChannel::class];
                $validationRules = ['required', 'phone:us'];
                break;
            default:
                $channel         = [];
                $validationRules = [];
        }

        $validator = Validator::make([
            'input' => $input,
        ], [
            'input' => $validationRules,
        ]);

        if ($validator->fails()) {
            return $validator;
        }

        return $channel;
    }
}
