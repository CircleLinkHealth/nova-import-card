<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\SharedModels\Services;

use Carbon\Carbon;
use CircleLinkHealth\Core\Contracts\ReportFormatter;
use CircleLinkHealth\Customer\Entities\SaasAccount;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\Customer\Relationships\PatientCareplanRelations;
use CircleLinkHealth\Customer\Repositories\NurseFinderEloquentRepository;
use CircleLinkHealth\Customer\Services\PatientReadRepository;
use CircleLinkHealth\PdfService\Services\PdfService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class CarePlanGeneratorService
{
    private ?string $blankPage = null;

    private CareplanService $carePlanService;
    private ReportFormatter $formatter;
    private PatientReadRepository $patientReadRepository;
    private PdfService $pdfService;
    private ?User $requester = null;
    private int $requesterId;

    public function __construct(ReportFormatter $formatter, PatientReadRepository $patientReadRepository, PdfService $pdfService, CareplanService $carePlanService)
    {
        $this->formatter             = $formatter;
        $this->patientReadRepository = $patientReadRepository;
        $this->pdfService            = $pdfService;
        $this->carePlanService       = $carePlanService;
    }

    /**
     * @throws \Exception
     *
     * @return \Spatie\MediaLibrary\Models\Media|null
     */
    public function pdfForUsers(int $requesterId, array $userIds, bool $letter)
    {
        $this->requesterId = $requesterId;

        /** @var Collection|User[] $users */
        $users = $this->getUsers($userIds);
        if ($users->isEmpty()) {
            return null;
        }

        $pageFileNames = [];
        foreach ($users as $user) {
            $viewParams = $this->prepareViewParams($user, $letter);

            if (true == $letter && 'pdf' == $user->carePlan->mode) {
                $viewParams['pdfCarePlan'] = $user->carePlan->pdfs->sortByDesc('created_at')->first();
            }

            $fileNameWithPath = $this->pdfService->createPdfFromView(
                'wpUsers.patient.multiview',
                $viewParams,
                null,
                config('services.serverless-pdf-generator.mail-vendor-envelope-options')
            );

            if ($users->count() > 1) {
                $fileNameWithPath = $this->addBlankPageIfNeeded($fileNameWithPath);
            }
            $pageFileNames[] = $fileNameWithPath;

            $this->markPrintDate($user, $letter);
        }

        $result = null;
        $len    = count($pageFileNames);
        if (0 === $len) {
            Log::critical('Something is wrong. No pdf files were generated.');
        } elseif (1 === $len) {
            $result = $pageFileNames[0];
        } else {
            $result = $this->pdfService->mergeFiles($pageFileNames);
        }

        return $result ? $this->addFileToMedia($result) : null;
    }

    public function renderForUser(int $requesterId, int $userId, bool $letter)
    {
        $this->requesterId = $requesterId;

        /** @var Collection|User[] $users */
        $users = $this->getUsers([$userId]);
        if ($users->isEmpty()) {
            return null;
        }

        $user       = $users->first();
        $viewParams = $this->prepareViewParams($user, $letter);

        return view('wpUsers.patient.multiview', $viewParams);
    }

    private function addBlankPageIfNeeded(?string $fileNameWithPath)
    {
        $pageCount = $this->pdfService->countPages($fileNameWithPath);
        // append blank page if needed
        if (0 != $pageCount % 2) {
            $fileNameWithPath = $this->pdfService->mergeFiles(
                [
                    $fileNameWithPath,
                    $this->getBlankPage(),
                ],
                $fileNameWithPath
            );
        }

        return $fileNameWithPath;
    }

    private function addFileToMedia(string $filePath)
    {
        return SaasAccount::whereSlug('circlelink-health')
            ->firstOrFail()
            ->addMedia($filePath)
            ->toMediaCollection('care-plans-pdf', 'media');
    }

    private function getBlankPage()
    {
        if ( ! $this->blankPage) {
            $this->blankPage = $this->pdfService->blankPage();
        }

        return $this->blankPage;
    }

    private function getRequester()
    {
        if (empty($this->requester)) {
            $this->requester = User::find($this->requesterId);
        }

        return $this->requester;
    }

    private function getUsers(array $userIds): Collection
    {
        return User::with(
            array_merge(
                PatientCareplanRelations::get(),
                [
                    'patientInfo',
                    'primaryPractice',
                    'inboundCalls' => function ($c) {
                        $c->with(['outboundUser'])
                            ->where('status', 'scheduled')
                            ->where('called_date', '=', null);
                    },
                    'billingProvider.user',
                ]
            )
        )
            ->has('patientInfo')
            ->findMany($userIds);
    }

    private function markPrintDate(User $patient, bool $letter)
    {
        if ($this->getRequester()->isAdmin() && true == $letter) {
            $careplanObj               = $patient->carePlan;
            $careplanObj->last_printed = Carbon::now()->toDateTimeString();
            if ( ! $careplanObj->first_printed) {
                $careplanObj->first_printed    = Carbon::now()->toDateTimeString();
                $careplanObj->first_printed_by = auth()->id();
            }
            $careplanObj->save();
        }
    }

    private function prepareViewParams(User $user, bool $letter)
    {
        $careplan = $this->formatter->formatDataForViewPrintCareplanReport($user);
        $careplan = $careplan[$user->id];
        if (empty($careplan)) {
            return false;
        }

        $gender            = $user->patientInfo->gender;
        $title             = 'm' === strtolower($gender) ? 'Mr.' : ('f' === strtolower($gender) ? 'Ms.' : null);
        $practiceNumber    = $user->primaryPractice->number_with_dashes;
        $assignedNurseName = optional(app(NurseFinderEloquentRepository::class)->find($user->id))->first_name;

        //if permanent assigned nurse does not exist, get nurse from scheduled call - CPM-1829
        if ( ! $assignedNurseName) {
            $call              = $user->inboundCalls->first();
            $assignedNurseName = $call ? optional($call->outboundUser)->first_name : null;
        }

        return [
            'careplans'         => [$user->id => $careplan],
            'isPdf'             => true,
            'letter'            => $letter,
            'problemNames'      => $careplan['problem'],
            'patient'           => $user,
            'careTeam'          => $user->careTeamMembers,
            'data'              => $this->carePlanService->careplan($user->id),
            'billingDoctor'     => $user->billingProviderUser(),
            'regularDoctor'     => $user->regularDoctorUser(),
            'title'             => $title,
            'practiceNumber'    => $practiceNumber,
            'assignedNurseName' => $assignedNurseName,
        ];
    }
}
