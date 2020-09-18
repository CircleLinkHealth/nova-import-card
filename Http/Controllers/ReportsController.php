<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CpmAdmin\Http\Controllers;

use CircleLinkHealth\Core\Exports\FromArray;
use CircleLinkHealth\Customer\Entities\Location;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Services\PatientReadRepository;
use CircleLinkHealth\Customer\Services\PrintPausedPatientLettersService;
use CircleLinkHealth\Customer\Services\ReportsService;
use CircleLinkHealth\SharedModels\Services\CpmProblemService;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class ReportsController extends Controller
{
    private $patientReadRepository;
    private $printPausedPatientLettersService;
    private $service;

    public function __construct(
        ReportsService $service,
        PrintPausedPatientLettersService $printPausedPatientLettersService,
        PatientReadRepository $patientReadRepository
    ) {
        $this->service                          = $service;
        $this->printPausedPatientLettersService = $printPausedPatientLettersService;
        $this->patientReadRepository            = $patientReadRepository;
    }

    public function excelReportUnreachablePatients()
    {
        $users = $this->patientReadRepository->unreachable()->fetch();

        $date     = date('Y-m-d H:i:s');
        $filename = 'CLH-Report-'.$date.'.xls';

        $rows = [];

        $headings = [
            'Patient id',
            'First Name',
            'Last Name',
            'Billing Provider',
            'Phone',
            'DOB',
            'CCM Status',
            'Gender',
            'Address',
            'City',
            'State',
            'Zip',
            'CCM Time',
            'Date Registered',
            'Date Paused',
            'Date Withdrawn',
            'Date Unreachable',
            'Site',
            'Caller id',
            'Location id',
            'Location Name',
            'Location Phone',
            'Location Address',
            'Location City',
            'Location State',
            'Location Zip',
        ];

        foreach ($users as $user) {
            $billingProvider = User::find($user->getBillingProviderId());
            //is billingProviderPhone to be used anywhere?
            if ( ! $billingProvider) {
                $billingProviderName  = '';
                $billingProviderPhone = '';
            } else {
                $billingProviderName  = $billingProvider->display_name;
                $billingProviderPhone = $billingProvider->getPhone();
            }

            $location = Location::find($user->getPreferredContactLocation());
            if ( ! $location) {
                $locationName    = '';
                $locationPhone   = '';
                $locationAddress = '';
                $locationCity    = '';
                $locationState   = '';
                $locationZip     = '';
            } else {
                $locationName    = $location->name;
                $locationPhone   = $location->phone;
                $locationAddress = $location->address_line_1;
                $locationCity    = $location->city;
                $locationState   = $location->state;
                $locationZip     = $location->postal_code;
            }

            $rows[] = [
                $user->id,
                $user->getFirstName(),
                $user->getLastName(),
                $billingProviderName,
                $user->getPhone(),
                $user->dob,
                $user->getCcmStatus(),
                $user->getGender(),
                $user->address,
                $user->city,
                $user->state,
                $user->zip,
                $user->monthlyTime,
                $user->patientInfo->user_registered,
                $user->patientInfo->date_paused,
                $user->patientInfo->date_withdrawn,
                $user->patientInfo->date_unreachable,
                $user->program_id,
                'Caller id',
                // provider_phone
                $user->getPreferredContactLocation(),
                $locationName,
                $locationPhone,
                $locationAddress,
                $locationCity,
                $locationState,
                $locationZip,
            ];
        }

        return (new FromArray($filename, $rows, $headings))->download($filename);
    }

    public function getPausedLettersFile(Request $request)
    {
        if ( ! $request->has('patientUserIds')) {
            throw new \InvalidArgumentException('patientUserIds is a required parameter', 422);
        }

        $viewOnly = $request->has('view');

        $userIdsToPrint = explode(',', $request['patientUserIds']);

        $fullPathToFile = $this->printPausedPatientLettersService->makePausedLettersPdf($userIdsToPrint, $viewOnly);

        return response()->file($fullPathToFile);
    }

    //PROGRESS REPORT
    public function index(
        Request $request,
        $patientId = false
    ) {
        $user             = User::find($patientId);
        $treating         = (app(CpmProblemService::class))->getDetails($user);
        $biometrics       = $this->service->getBiometricsToMonitor($user);
        $biometrics_data  = [];
        $biometrics_array = [];

        foreach ($biometrics as $biometric) {
            $biometrics_data[$biometric] = $this->service->getBiometricsData(str_replace(' ', '_', $biometric), $user);
        }

        foreach ($biometrics_data as $key => $value) {
            $value    = $value->all();
            $bio_name = $key;
            if (null != $value) {
                $first   = reset($value);
                $last    = end($value);
                $changes = $this->service
                    ->biometricsIndicators(
                        intval($last->Avg),
                        intval($first->Avg),
                        $bio_name,
                        (new ReportsService())->getTargetValueForBiometric($bio_name, $user)
                    );

                $biometrics_array[$bio_name]['change']      = $changes['change'];
                $biometrics_array[$bio_name]['progression'] = $changes['progression'];
                $biometrics_array[$bio_name]['status']      = (isset($changes['status']))
                    ? $changes['status']
                    : 'Unchanged';
                //$changes['bio']= $bio_name;debug($changes);
                $biometrics_array[$bio_name]['lastWeekAvg'] = intval($last->Avg);
            }//debug($biometrics_array);

            $count                               = 1;
            $biometrics_array[$bio_name]['data'] = '';
            $biometrics_array[$bio_name]['max']  = -1;
            //$first = reset($array);
            if ($value) {
                foreach ($value as $key => $value) {
                    $biometrics_array[$bio_name]['unit'] = $this->service->biometricsUnitMapping(
                        str_replace(
                            '_',
                            ' ',
                            $bio_name
                        )
                    );
                    $biometrics_array[$bio_name]['target'] = $this->service->getTargetValueForBiometric(
                        $bio_name,
                        $user,
                        false
                    );
                    $biometrics_array[$bio_name]['reading'] = intval($value->Avg);
                    if (intval($value->Avg) > $biometrics_array[$bio_name]['max']) {
                        $biometrics_array[$bio_name]['max'] = intval($value->Avg);
                    }
                    $biometrics_array[$bio_name]['data'] .= '{ id:'.$count.', Week:\''.$value->day.'\', Reading:'.intval(
                        $value->Avg
                    ).'} ,';
                    ++$count;
                }
            } else {
                //no data
                unset($biometrics_array[$bio_name]);
            }
        }//dd($biometrics_array);

        // get provider
        $provider = User::find($user->getLeadContactID());

        //Medication Tracking:
        $medications = $this->service->getMedicationStatus($user, false);

        $data = [
            'treating'                => $treating,
            'patientId'               => $patientId,
            'patient'                 => $user,
            'provider'                => $provider,
            'medications'             => $medications,
            'tracking_biometrics'     => $biometrics_array,
            'noLiveCountTimeTracking' => true,
        ];

        return view('cpm-admin::wpUsers.patient.progress', $data);
    }

    public function pausedPatientsLetterPrintList()
    {
        $patients = false;

        $pausedPatients = $this->printPausedPatientLettersService->getPausedPatients();

        if ($pausedPatients->isNotEmpty()) {
            $patients = $pausedPatients->toJson();
        }

        $url = route('get.paused.letters.file').'?patientUserIds=';

        return view('cpm-admin::patient.printPausedPatientsLetters', compact(['patients', 'url']));
    }

    public function progress(
        Request $request,
        $id = false
    ) {
        if ('mobi' == $request->header('Client')) {
            // get and validate current user
            \JWTAuth::setIdentifier('id');
            $wpUser = \JWTAuth::parseToken()->authenticate();
            if ( ! $wpUser) {
                return response()->json(['error' => 'invalid_credentials'], 401);
            }
        } else {
            // get user
            $wpUser = User::find($id);
            if ( ! $wpUser) {
                return response('User not found', 401);
            }
        }

        $feed = $this->service->progress($wpUser->id);

        return json_encode($feed);
    }
}
