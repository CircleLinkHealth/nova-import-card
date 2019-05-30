<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices\ValueObjects;

use Carbon\Carbon;

class NurseInvoiceDisputeDeadline
{
    /**
     * The default deadline for nurses to submit invoice disputes.
     * The format is '{DAY} {HOUR}:{MINUTES}{AM/PM}'.
     *
     * @var string
     */
    const DEFAULT_NURSE_INVOICE_DISPUTE_SUBMISSION_DEADLINE_DAY_AND_TIME = '3 11:59PM';
    /**
     * The key name for storing the deadline for nurses to submit disputes for monthly invoices.
     *
     * @var string
     */
    const NURSE_INVOICE_DISPUTE_SUBMISSION_DEADLINE_KEY = 'nurse_invoice_dispute_submission_deadline';

    /**
     * @param Carbon $invoiceMonth
     *
     * @return Carbon
     */
    public static function forInvoiceOfMonth(Carbon $invoiceMonth)
    {
        return (new static())->calculateDeadline($invoiceMonth);
    }

    /**
     * Gets the deadline from the app's config (set by admin from nova page).
     *
     * @return array
     */
    private function adminDefinedDeadline()
    {
        $deadline = explode(
            ' ',
            getAppConfig(
                self::NURSE_INVOICE_DISPUTE_SUBMISSION_DEADLINE_KEY,
                self::DEFAULT_NURSE_INVOICE_DISPUTE_SUBMISSION_DEADLINE_DAY_AND_TIME
            )
        );

        return [
            'day'  => $deadline[0] ?? null,
            'time' => $deadline[1] ?? null,
        ];
    }

    /**
     * @param Carbon $invoiceMonth
     *
     * @return Carbon
     */
    private function calculateDeadline(Carbon $invoiceMonth)
    {
        $deadline = $this->deadline();

        $reviewMonth = $invoiceMonth->copy()->addMonth();

        //If the deadline is set to a day that does not exist in this month, use last day of month.
        //Example: deadline is set to 31st of the month, but month is April, which has 30 days
        if (($lastDayOfMonth = $reviewMonth->copy()->endOfMonth()->day) < $deadline['day']) {
            $deadline['day'] = $lastDayOfMonth;
        }

        return $reviewMonth->day($deadline['day'])->setTimeFromTimeString($deadline['time']);
    }

    /**
     * Returns the admin defined deadline to approve invoices. If the date was not set by the admin, it returns the
     * default deadline.
     *
     * @return array
     */
    private function deadline()
    {
        $deadline = $this->adminDefinedDeadline();

        $validator = $this->validator($deadline);

        if ($validator->passes()) {
            return $deadline;
        }

        return $this->defaultDeadline();
    }

    /**
     * Gets the default deadline.
     *
     * @return array
     */
    private function defaultDeadline()
    {
        $deadline = explode(' ', self::DEFAULT_NURSE_INVOICE_DISPUTE_SUBMISSION_DEADLINE_DAY_AND_TIME);

        return [
            'day'  => $deadline[0] ?? null,
            'time' => $deadline[1] ?? null,
        ];
    }

    /**
     * Get a validator instance for the deadline.
     *
     * @param array $deadline
     *
     * @return \Illuminate\Validation\Validator
     */
    private function validator(array $deadline)
    {
        return \Validator::make(
            $deadline,
            [
                'day'  => 'required|numeric|min:1|max:31',
                'time' => 'required|date_format:"h:iA"',
            ]
        );
    }
}
