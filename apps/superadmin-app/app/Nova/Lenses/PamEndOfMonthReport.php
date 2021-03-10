<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Nova\Lenses;

use App\Nova\Actions\ExportPamEndOfMonthReport;
use App\Nova\Filters\MonthFilter;
use Carbon\Carbon;
use CircleLinkHealth\Customer\Entities\Patient;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Laravel\Nova\Fields\Text;
use Laravel\Nova\Http\Requests\LensRequest;
use Laravel\Nova\Lenses\Lens;

class PamEndOfMonthReport extends Lens
{
    public $name = 'PAM End Of Month Report';

    /**
     * Get the actions available on the lens.
     *
     * @return array
     */
    public function actions(Request $request)
    {
        return [(new ExportPamEndOfMonthReport())->withDisk('media')];
    }

    /**
     * Get the cards available on the lens.
     *
     * @return array
     */
    public function cards(Request $request)
    {
        return [];
    }

    /**
     * Get the fields available to the lens.
     *
     * @return array
     */
    public function fields(Request $request)
    {
        return [
            Text::make('Nurse Name', 'Nurse Name')->sortable(),
            Text::make('Patient Name', 'Patient Name')->sortable(),
            Text::make('Practice', 'Practice')->sortable(),
            Text::make('Last Call', 'Last Call')->sortable(),
            Text::make('CCM Time', 'CCM Time')->sortable(),
            Text::make('CCM (RHC/FQHC) Time', 'CCM (RHC/FQHC) Time')->sortable(),
            Text::make('PCM Time', 'PCM Time')->sortable(),
            Text::make('BHI Time', 'BHI Time')->sortable(),
            Text::make('RPM Time', 'RPM Time')->sortable(),
            Text::make('Successful Calls', 'Successful Calls')->sortable(),
        ];
    }

    /**
     * Get the filters available for the lens.
     *
     * @return array
     */
    public function filters(Request $request)
    {
        return [new MonthFilter()];
    }

    /**
     * Get the query builder / paginator for the lens.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     */
    public static function query(LensRequest $request, $query): Builder
    {
        $month     = self::getFilterMonth($request);
        $startTime = $month->startOfMonth()->toDateTimeString();
        $endTime   = $month->endOfMonth()->toDateTimeString();

        return $request->withOrdering($request->withFilters(
            $query->selectRaw(self::getSelectRaw($startTime, $endTime))
                ->fromRaw('calls')
                ->join('users as patient_users', 'calls.inbound_cpm_id', '=', 'patient_users.id')
                ->join('practices as patient_practices', 'patient_users.program_id', '=', 'patient_practices.id')
                ->whereRaw(self::getWhereRaw($startTime, $endTime))
        ));
    }

    /**
     * Get the URI key for the lens.
     */
    public function uriKey(): string
    {
        return 'pam-end-of-month-report';
    }

    private static function getFilterMonth(LensRequest $request): Carbon
    {
        $result  = now();
        $filters = $request->filters();
        if ( ! empty($filters)) {
            foreach ($filters as $filter) {
                if ($filter->filter instanceof MonthFilter) {
                    $result = Carbon::parse($filter->value);
                }
            }
        }

        return $result;
    }

    private static function getSelectRaw(string $startTime, string $endTime): string
    {
        return "
        calls.id,
        (select display_name from users where id = calls.outbound_cpm_id) as `Nurse Name`,
        patient_users.display_name as `Patient Name`,
        patient_practices.display_name as `Practice`,
        calls.called_date as `Last Call`,
        SEC_TO_TIME(ifnull((select sum(duration) from lv_activities where patient_id = calls.inbound_cpm_id and chargeable_service_id = (select id from chargeable_services where display_name = 'CCM') and performed_at BETWEEN '$startTime' and '$endTime'), 0)) as `CCM Time`,
        SEC_TO_TIME(ifnull((select sum(duration) from lv_activities where patient_id = calls.inbound_cpm_id and chargeable_service_id = (select id from chargeable_services where display_name = 'CCM (RHC/FQHC)') and performed_at BETWEEN '$startTime' and '$endTime'), 0)) as `CCM (RHC/FQHC) Time`,
        SEC_TO_TIME(ifnull((select sum(duration) from lv_activities where patient_id = calls.inbound_cpm_id and chargeable_service_id = (select id from chargeable_services where display_name = 'PCM') and performed_at BETWEEN '$startTime' and '$endTime'), 0)) as `PCM Time`,
        SEC_TO_TIME(ifnull((select sum(duration) from lv_activities where patient_id = calls.inbound_cpm_id and chargeable_service_id = (select id from chargeable_services where display_name = 'BHI') and performed_at BETWEEN '$startTime' and '$endTime'), 0)) as `BHI Time`,
        SEC_TO_TIME(ifnull((select sum(duration) from lv_activities where patient_id = calls.inbound_cpm_id and chargeable_service_id = (select id from chargeable_services where display_name = 'RPM') and performed_at BETWEEN '$startTime' and '$endTime'), 0)) as `RPM Time`,
        (select count(id) from calls c where inbound_cpm_id = calls.inbound_cpm_id and `status` = 'reached' and called_date is not null and called_date BETWEEN '$startTime' and '$endTime') as `Successful Calls`
        ";
    }

    private static function getWhereRaw(string $startTime, string $endTime): string
    {
        $patientModelClass = Patient::class;

        return "
        patient_practices.is_demo = 0
        and called_date is not null
        and calls.called_date BETWEEN '$startTime' and '$endTime'
        and calls.called_date in (
            select max(called_date) 
            from calls 
            where called_date BETWEEN '$startTime' and '$endTime' 
            group by inbound_cpm_id)
        and ifnull((select old_value from revisions where revisionable_id = calls.inbound_cpm_id and revisionable_type = '$patientModelClass' and `key` = 'ccm_status' and created_at >= '$endTime' order by id asc limit 1), (select ccm_status from patient_info where user_id = calls.inbound_cpm_id)) = 'enrolled'
        ";
    }
}
