<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\CcdApi\Aprima;

use App\CarePlan;
use App\CLH\Repositories\CCDImporterRepository;
use App\Contracts\Repositories\ActivityRepository;
use App\Contracts\Repositories\AprimaCcdApiRepository;
use App\Contracts\Repositories\CcdaRepository;
use App\Contracts\Repositories\CcmTimeApiLogRepository;
use App\Contracts\Repositories\UserRepository;
use App\ForeignId;
use App\Http\Controllers\Controller;
use App\Models\CCD\ValidatesQAImportOutput;
use App\Models\MedicalRecords\Ccda;
use App\Note;
use App\PatientReports;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\CarePerson;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Http\Request;

class CcdApiController extends Controller
{
    use ValidatesQAImportOutput;
    protected $activities;

    protected $api;
    protected $ccda;
    protected $ccmTime;
    private $importer;
    private $users;

    public function __construct(
        ActivityRepository $activityRepository,
        CCDImporterRepository $repo,
        CcdaRepository $ccdaRepository,
        CcmTimeApiLogRepository $ccmTime,
        AprimaCcdApiRepository $aprimaCcdApiRepository,
        UserRepository $users
    ) {
        $this->activities = $activityRepository;
        $this->ccda       = $ccdaRepository;
        $this->ccmTime    = $ccmTime;
        $this->api        = $aprimaCcdApiRepository;
        $this->importer   = $repo;
        $this->users      = $users;
    }

    public function getApiUserLocation($user)
    {
        $apiUserLocation = $user->locations;

        try {
            $locationId = $apiUserLocation[0]->pivot->location_id;
        } catch (\Exception $e) {
            return response()->json('Could not resolve a Location from your User.', 400);
        }

        return $locationId;
    }

    public function getCcmTime(Request $request)
    {
        if ( ! \Session::has('apiUser')) {
            return response()->json(['error' => 'Authentication failed.'], 403);
        }

        $user = \Session::get('apiUser');

        //If there is no start date, set a really old date to include all activities
        $startDate = empty($from = $request->input('start_date'))
            ? Carbon::createFromDate('1990', '01', '01')
            : Carbon::parse($from)->startOfDay();

        //If there is no end date, set tomorrow's date to include all activities
        $endDate = empty($to = $request->input('end_date'))
            ? Carbon::tomorrow()
            : Carbon::parse($to)->endOfDay();

        $sendAll = filter_var($request->input('send_all'), FILTER_VALIDATE_BOOLEAN);

        $locationId = $this->getApiUserLocation($user);

        $patientAndProviderIds = $this->api
            ->getPatientAndProviderIdsByLocationAndForeignSystem($locationId, ForeignId::APRIMA);

        foreach ($patientAndProviderIds as $ids) {
            $activities = $this->activities->getCcmActivities(
                $ids->clhPatientUserId,
                $ids->clhProviderUserId,
                $startDate,
                $endDate,
                $sendAll
            );

            if ($activities->isEmpty()) {
                continue;
            }

            $careEvents = $activities->map(function ($careEvent) {
                $this->ccmTime->logSentActivity(['activity_id' => $careEvent->id], ['activity_id' => $careEvent->id]);

                return [
                    'servicePerson'    => $careEvent->servicePerson,
                    'startingDateTime' => $careEvent->startingDateTime,
                    'length'           => $careEvent->length,
                    'lengthUnit'       => $careEvent->lengthUnit,
                    'commentString'    => $careEvent->commentString,
                    //                    'comment' => $careEvent->comment,
                    'alertProvider' => false,
                ];
            });

            $results[] = [
                'patientId'  => $ids->patientId,
                'providerId' => $ids->providerId,
                'careEvents' => $careEvents,
            ];
        }

        return isset($results)
            ? response()->json($results, 200)
            : response()->json([], 200);
    }

    public function notes(Request $request)
    {
        if ( ! \Session::has('apiUser')) {
            return response()->json(['error' => 'Authentication failed.'], 403);
        }

        $user = \Session::get('apiUser');

        //If there is no start date, set a really old date to include all activities
        $startDate = empty($from = $request->input('start_date'))
            ? Carbon::createFromDate('1990', '01', '01')
            : Carbon::parse($from)->startOfDay();

        //If there is no end date, set tomorrow's date to include all activities
        $endDate = empty($to = $request->input('end_date'))
            ? Carbon::tomorrow()
            : Carbon::parse($to)->endOfDay();

        $sendAll = filter_var($request->input('send_all'), FILTER_VALIDATE_BOOLEAN);

        $locationId = $this->getApiUserLocation($user);

        $pendingReports = PatientReports::where('location_id', $locationId)
            ->whereFileType(Note::class)
            ->whereBetween('created_at', [
                $startDate,
                $endDate,
            ]);
        if ($sendAll) {
            $pendingReports->withTrashed();
        }
        $pendingReports = $pendingReports->get();

        if ($pendingReports->isEmpty()) {
            return response()->json([], 200);
        }

        $json = [];

        foreach ($pendingReports as $report) {
            //Get patient's lead provider
            $provider = CarePerson::whereUserId($report->patient_id)
                ->whereType('lead_contact')
                ->first();

            if (empty($provider)) {
                continue;
            }

            //Get lead provider's foreign_id
            $foreignId_obj = ForeignId::where('system', ForeignId::APRIMA)
                ->where('user_id', $provider->member_user_id)
                ->where('location_id', $locationId)
                ->first();

            if (empty($foreignId_obj)) {
                continue;
            }

            if (empty($report->file_base64)) {
                continue;
            }

            if ($foreignId_obj->foreign_id) {
                $json[] = [
                    'patientId'  => $report->patient_mrn,
                    'providerId' => $foreignId_obj->foreign_id,
                    'comment'    => null,
                    'file'       => $report->file_base64,
                    'fileType'   => $report->file_type,
                    'created_at' => $report->created_at->toDateTimeString(),
                ];
            }
        }

        PatientReports::where('location_id', $locationId)
            ->whereFileType(Note::class)
            ->delete();

        return response()->json($json, 200, ['fileCount' => count($json)]);
    }

    /**
     * This is to help notify us of the status of CCDs we receive.
     *
     * @param \CircleLinkHealth\Customer\Entities\User $user
     * @param \App\Models\MedicalRecords\Ccda          $ccda
     * @param $status
     * @param null       $line
     * @param null       $errorMessage
     * @param mixed|null $providerInfo
     */
    public function notifyAdmins(
        User $user,
        Ccda $ccda,
        $providerInfo = null,
        $status,
        $line = null,
        $errorMessage = null
    ) {
        $link = route('import.ccd.remix');

        sendSlackMessage('#ccd-file-status', "Aprima sent a CCD. It went {$status}. \n Please visit {$link} to import.");
    }

    public function reports(Request $request)
    {
        if ( ! \Session::has('apiUser')) {
            return response()->json(['error' => 'Authentication failed.'], 403);
        }

        $user = \Session::get('apiUser');

        //If there is no start date, set a really old date to include all activities
        $startDate = empty($from = $request->input('start_date'))
            ? Carbon::createFromDate('1990', '01', '01')
            : Carbon::parse($from)->startOfDay();

        //If there is no end date, set tomorrow's date to include all activities
        $endDate = empty($to = $request->input('end_date'))
            ? Carbon::tomorrow()
            : Carbon::parse($to)->endOfDay();

        $sendAll = filter_var($request->input('send_all'), FILTER_VALIDATE_BOOLEAN);

        $locationId = $this->getApiUserLocation($user);

        $pendingReports = PatientReports::where('location_id', $locationId)
            ->whereFileType(CarePlan::class)
            ->whereBetween('created_at', [
                $startDate,
                $endDate,
            ]);
        if ($sendAll) {
            $pendingReports->withTrashed();
        }
        $pendingReports = $pendingReports->get();

        if ($pendingReports->isEmpty()) {
            return response()->json([], 200);
        }

        $json = [];

        foreach ($pendingReports as $report) {
            //Get patient's lead provider
            $provider = CarePerson::whereUserId($report->patient_id)
                ->whereType('lead_contact')
                ->first();

            if (empty($provider)) {
                continue;
            }

            //Get lead provider's foreign_id
            $foreignId_obj = ForeignId::where('system', ForeignId::APRIMA)
                ->where('user_id', $provider->member_user_id)
                ->where('location_id', $locationId)
                ->first();

            if (empty($foreignId_obj)) {
                continue;
            }

            if (empty($report->file_base64)) {
                continue;
            }

            if ($foreignId_obj->foreign_id) {
                $json[] = [
                    'patientId'  => $report->patient_mrn,
                    'providerId' => $foreignId_obj->foreign_id,
                    'comment'    => null,
                    'file'       => $report->file_base64,
                    'fileType'   => $report->file_type,
                    'created_at' => $report->created_at->toDateTimeString(),
                ];
            }
        }

        PatientReports::where('location_id', $locationId)
            ->whereFileType(CarePlan::class)
            ->delete();

        return response()->json($json, 200, ['fileCount' => count($json)]);
    }

    public function uploadCcd(Request $request)
    {
        if ( ! \Session::has('apiUser')) {
            return response()->json(['error' => 'Authentication failed.'], 403);
        }

        $user = \Session::get('apiUser');

        if ( ! $user->hasPermissionForSite('post-ccd-to-api', $user->getPrimaryPracticeId())) {
            return response()->json(['error' => 'You are not authorized to submit CCDs to this API.'], 403);
        }

        if ( ! $request->filled('file')) {
            return response()->json(['error' => 'file is a required field.'], 422);
        }

        if ( ! $request->filled('provider')) {
            return response()->json(['error' => 'provider is a required field.'], 422);
        }

        try {
            $providerInput   = \GuzzleHttp\json_decode($request->input('provider'), true);
            $providerJsonStr = $request->input('provider');
        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid json in provider field.'], 400);
        }

        if ( ! empty($providerInput['firstName']) && ! empty($providerInput['lastName'])) {
            //Check if the provider exists
            $provider = $this->users->findWhere([
                'display_name' => $providerInput['firstName'].' '.$providerInput['lastName'],
            ]);
        }

        $locationId = $this->getApiUserLocation($user);

        if (isset($provider[0])) {
            $provider = \GuzzleHttp\json_decode($provider[0], true);

            try {
                ForeignId::updateOrCreate([
                    'user_id'     => $provider['id'],
                    'system'      => ForeignId::APRIMA,
                    'location_id' => $locationId,
                ], [
                    'foreign_id' => $providerInput['providerId'],
                ]);
            } catch (\Exception $e) {
                return response()->json(['error' => 'providerId is already associated with another clinic.'], 400);
            }
        }

        $programId = $user->program_id;

        try {
            $xml = base64_decode($request->input('file'));
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to base64_decode CCD.'], 400);
        }

        $ccdObj = $this->ccda->create([
            'user_id'   => $user->id,
            'vendor_id' => 1,
            'xml'       => $xml,
            'source'    => Ccda::API,
        ]);

        //We are saving the JSON CCD after we save the XML, just in case Parsing fails
        //If Parsing fails we let ourselves know, but not Aprima.
        try {
            $json = $this->importer->toJson($xml);

            $providerId = empty($provider['id'])
                ? null
                : $provider['id'];

            $ccdObj->import();
        } catch (\Exception $e) {
            if (isProductionEnv()) {
                $this->notifyAdmins(
                    $user,
                    $ccdObj,
                    $providerJsonStr,
                    'bad',
                    __METHOD__.' '.__LINE__,
                    $e->getMessage()
                );
            }

            return response()->json(['message' => 'CCD uploaded successfully.'], 201);
        }

        if (isProductionEnv()) {
            $this->notifyAdmins($user, $ccdObj, $providerJsonStr, 'well');
        }

        return response()->json(['message' => 'CCD uploaded successfully.'], 201);
    }
}
