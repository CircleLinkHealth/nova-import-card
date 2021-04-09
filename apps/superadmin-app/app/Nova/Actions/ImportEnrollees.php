<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Actions;

use App\Nova\Importers\Enrollees;
use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\SaasAccount;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Bus\Queueable;
use Illuminate\Http\UploadedFile;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Laravel\Nova\Actions\Action;
use Laravel\Nova\Fields\ActionFields;
use Laravel\Nova\Fields\File;
use Laravel\Nova\Fields\Select;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\MediaLibrary\Models\Media;

class ImportEnrollees extends Action
{
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    const ACTION_ASSIGN_ENROLLEES_TO_CA              = 'assign_enrollees_to_ca';
    const ACTION_CREATE_ENROLLEES                    = 'create_enrollees';
    const ACTION_CREATE_ENROLLEES_FROM_PRACTICE_PULL = 'create_enrollees_from_practice_pull';
    const ACTION_MARK_AUTO_ENROLLMENT                = 'mark_for_auto_enrollment';
    const ACTION_MARK_INELIGIBLE                     = 'mark_as_ineligible';
    const LABEL_ASSIGN_ENROLLEES_TO_CA               = 'Assign Patients to Care Ambassador';
    const LABEL_CREATE_ENROLLEES                     = 'Create Patients from CSV (non-importable)';
    const LABEL_CREATE_ENROLLEES_FROM_PRACTICE_PULL  = 'Create Patients from Practice Pull Data';
    const LABEL_MARK_AUTO_ENROLLMENT                 = 'Mark Patients for Self Enrollment';
    const LABEL_MARK_INELIGIBLE                      = 'Mark Patients as Ineligible';

    /**
     * Indicates if this action is only available on the resource detail view.
     *
     * @var bool
     */
    public $onlyOnIndex = true;

    public $standalone = true;

    /**
     * @var array
     */
    private $fields;

    public function __construct(array $fields = [])
    {
        $this->fields = $fields;
    }

    /**
     * Get the fields available on the action.
     *
     * @return array
     */
    public function fields()
    {
        $practices = Practice::whereIn('id', auth()->user()->viewableProgramIds())
            ->activeBillable()
            ->pluck('display_name', 'id')
            ->toArray();

        $ambassadors = User::ofType('care-ambassador')
            ->pluck('display_name', 'id')
            ->toArray();

        return [
            Select::make('Action Type', 'action_type')->options([
                self::ACTION_CREATE_ENROLLEES                    => self::LABEL_CREATE_ENROLLEES,
                self::ACTION_CREATE_ENROLLEES_FROM_PRACTICE_PULL => self::LABEL_CREATE_ENROLLEES_FROM_PRACTICE_PULL,
                self::ACTION_MARK_AUTO_ENROLLMENT                => self::LABEL_MARK_AUTO_ENROLLMENT,
                self::ACTION_MARK_INELIGIBLE                     => self::LABEL_MARK_INELIGIBLE,
                self::ACTION_ASSIGN_ENROLLEES_TO_CA              => self::LABEL_ASSIGN_ENROLLEES_TO_CA,
            ]),
            File::make('File')
                ->rules('required'),
            Select::make('Practice', 'practice_id')->options($practices)->withModel(Practice::class),
            Select::make('Care Ambassador (only for assign action type)', 'ca_id')->options($ambassadors),
        ];
    }

    /**
     * Perform the action on the given models.
     *
     * @return mixed
     */
    public function handle(ActionFields $fields, Collection $models)
    {
        /** @var UploadedFile $file */
        $file = $fields->file;

        $class = $this->getImporter($actionType = $fields->action_type);

        if (is_null($class)) {
            return Action::message("Something went wrong. Action: $actionType not found.");
        }

        $fileName = "mark_for_self_enrollment_list_for_practice:$fields->practice_id.{$file->getClientOriginalName()}";

        $fileFromMedia =   $this->uploadAndReturnFile($file->getRealPath(),  $fileName);

        if ($fileFromMedia){
            Excel::import(new $class($fields->practice_id, $fileName, $fields->ca_id), $fileFromMedia->getPath(),'media', \Maatwebsite\Excel\Excel::XLSX);
        }else{
            Excel::import(new $class($fields->practice_id, $file->getClientOriginalName(), $fields->ca_id), $file);
        }

        return Action::message('It worked!');
    }

    private function uploadAndReturnFile(string $filePath, string $fileName): Media
    {
        return SaasAccount::whereSlug('circlelink-health')
            ->first()
            ->addMedia($filePath)
            ->toMediaCollection($fileName);
    }

    /**
     * Works for inside of card.
     *
     * @return string
     */
    public function name()
    {
        return __('Import CSV');
    }

    public function uriKey(): string
    {
        return 'import-enrollees';
    }

    private function actionImporterClassmap(): array
    {
        return [
            self::ACTION_MARK_AUTO_ENROLLMENT                => Enrollees\MarkEnrolleesForSelfEnrollment::class,
            self::ACTION_CREATE_ENROLLEES_FROM_PRACTICE_PULL => Enrollees\CreateEnrolleesFromPracticePull::class,
            self::ACTION_ASSIGN_ENROLLEES_TO_CA              => Enrollees\AssignEnrolleesToCareAmbassador::class,
            self::ACTION_CREATE_ENROLLEES                    => Enrollees\CreateNonImportableEnrollees::class,
            self::ACTION_MARK_INELIGIBLE                     => Enrollees\MarkEnrollesAsIneligible::class,
        ];
    }

    private function getImporter(string $actionType): ?string
    {
        return $this->actionImporterClassmap()[$actionType] ?? null;
    }
}
