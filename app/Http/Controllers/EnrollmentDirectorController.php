<?php

namespace App\Http\Controllers;

use App\Enrollee;
use App\Filters\EnrolleeFilters;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EnrollmentDirectorController extends Controller
{
    public function index()
    {
        return view('admin.ca-director.index');
    }

    public function getEnrollees(Request $request, EnrolleeFilters $filters)
    {

        $fields = ['*'];

        $byColumn  = $request->get('byColumn');
        $query     = $request->get('query');
        $limit     = $request->get('limit');
        $orderBy   = $request->get('orderBy');
        $ascending = $request->get('ascending');
        $page      = $request->get('page');

        $data = Enrollee::filter($filters)->select($fields);

        $count = $data->count();

        $data->limit($limit)
             ->skip($limit * ($page - 1));

        if (isset($orderBy)) {
            $direction = $ascending == 1
                ? 'ASC'
                : 'DESC';
            $data->orderBy($orderBy, $direction);
        }

        $results = $data->get()->toArray();

        return [
            'data'  => $results,
            'count' => $count,
        ];

    }

    public function getCareAmbassadors()
    {
        $ambassadors = User::ofType('care-ambassador')
                           ->select(['id', 'display_name'])
                           ->get();

        return ['data' => $ambassadors->toArray()];
    }

    protected function filterByColumn($data, $queries)
    {
        return $data->where(function ($q) use ($queries) {
            foreach ($queries as $field => $query) {
                if (is_string($query)) {
                    $q->where($field, 'LIKE', "%{$query}%");
                } else {
                    $start = Carbon::createFromFormat('Y-m-d', $query['start'])->startOfDay();
                    $end   = Carbon::createFromFormat('Y-m-d', $query['end'])->endOfDay();

                    $q->whereBetween($field, [$start, $end]);
                }
            }
        });
    }

    protected function filter($data, $query, $fields)
    {
        return $data->where(function ($q) use ($query, $fields) {
            foreach ($fields as $index => $field) {
                $method = $index
                    ? 'orWhere'
                    : 'where';
                $q->{$method}($field, 'LIKE', "%{$query}%");
            }
        });
    }
}
