<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Services;

use Carbon\Carbon;
use CircleLinkHealth\NurseInvoices\Entities\NurseInvoice;

class SaveInvoicesService
{
    /**
     * @param $user
     * @param $viewModel
     * @param Carbon $startDate
     *
     * @return
     */
    public function saveInvoiceData($user, $viewModel, Carbon $startDate)
    {
        return NurseInvoice::updateOrCreate(
            [
                'month_year'    => $startDate,
                'nurse_info_id' => $user->nurseInfo->id,
            ],
            [
                'invoice_data' => $viewModel->toArray(),
            ]
        );
    }
}
