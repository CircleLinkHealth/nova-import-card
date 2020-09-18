<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\CpmAdmin\Http\Controllers\Enrollment;

use Carbon\Carbon;
use CircleLinkHealth\Core\Traits\ApiReturnHelpers;
use CircleLinkHealth\CpmAdmin\Http\Resources\EnrolleeCsvResource;
use CircleLinkHealth\SharedModels\Entities\EnrolleeView;
use CircleLinkHealth\SharedModels\Filters\EnrolleeFilters;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class EnrollmentConsentController extends Controller
{
    use ApiReturnHelpers;

    /**
     * @return mixed
     */
    public function index(Request $request, EnrolleeFilters $filters)
    {
        $fields = ['*'];

        $byColumn  = $request->get('byColumn');
        $query     = $request->get('query');
        $limit     = $request->get('limit');
        $orderBy   = $request->get('orderBy');
        $ascending = $request->get('ascending');
        $page      = $request->get('page');

        $data = EnrolleeView::filter($filters)->select($fields);

        $count = $data->count();

        $data->limit($limit)
            ->skip($limit * ($page - 1));

        $now = Carbon::now()->toDateString();

        if (isset($orderBy)) {
            $direction = 1 == $ascending
                ? 'ASC'
                : 'DESC';
            $data->orderBy($orderBy, $direction);
        }

        $filtersInput = $filters->filters();

        if ($filters->isCsv()) {
            return EnrolleeCsvResource::collection($data->paginate($filtersInput['rows']));
        }

        $results = $data->get()->toArray();

        return [
            'data'  => $results,
            'count' => $count,
        ];
    }

    public function makeEnrollmentReport()
    {
        return view('admin.reports.enrollment.enrollment-list');
    }

   
}
