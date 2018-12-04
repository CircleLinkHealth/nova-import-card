<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\User;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;

class PatientsForInsuranceCheck extends Controller
{
    public function make()
    {
        $date = Carbon::today()->subMonth(5);

        $users = User::ofType('participant')
            ->with([
                'ccdInsurancePolicies',
                'primaryPractice',
            ])
            ->whereHas('patientInfo', function ($q) {
                $q->where('ccm_status', '=', 'enrolled');
            })
            ->where('created_at', '>=', $date)
            ->get()->map(function ($user) {
                return [
                    'name'             => $user->getFullName(),
                    'dob'              => $user->getBirthDate(),
                    'billing_provider' => optional($user->billingProviderUser())->getFullName(),
                    'practice'         => $user->primaryPractice->display_name,
                    'insurance_1'      => $user->ccdInsurancePolicies[0]->name ?? '',
                    'insurance_2'      => $user->ccdInsurancePolicies[1]->name ?? '',
                    'insurance_3'      => $user->ccdInsurancePolicies[2]->name ?? '',
                ];
            });

        Excel::create("{$date->toDateString()} - Patients For Insurance Check", function ($excel) use (
            $users
        ) {
            $excel->sheet('Patients', function ($sheet) use (
                $users
            ) {
                $sheet->fromArray($users);
            });
        })->export('xls');
    }
}
