<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\NurseInvoices\Helpers;

use Carbon\Carbon;

class NurseInvoiceDisputeDeadline
{
    /**
     * Minutes to cache for.
     */
    const CACHE_FOR_MINUTES = 2;
    /**
     * Default preferredOrDefaultDeadline day of month number.
     *
     * @var string
     */
    const DEFAULT_NURSE_INVOICE_DISPUTE_SUBMISSION_DEADLINE_DAY = 3;
    /**
     * The default preferredOrDefaultDeadline for nurses to submit invoice disputes.
     * The format is '{DAY} {HOUR}:{MINUTES}{AM/PM}'.
     *
     * @var string
     */
    const DEFAULT_NURSE_INVOICE_DISPUTE_SUBMISSION_DEADLINE_DAY_AND_TIME = self::DEFAULT_NURSE_INVOICE_DISPUTE_SUBMISSION_DEADLINE_DAY.' '.self::DEFAULT_NURSE_INVOICE_DISPUTE_SUBMISSION_DEADLINE_TIME;

    /**
     * Default preferredOrDefaultDeadline hour of day '{HOUR}:{MINUTES}{AM/PM}'.
     *
     * @var string
     */
    const DEFAULT_NURSE_INVOICE_DISPUTE_SUBMISSION_DEADLINE_TIME = '11:59PM';
    /**
     * The key name for storing the preferredOrDefaultDeadline for nurses to submit disputes for monthly invoices.
     *
     * @var string
     */
    const NURSE_INVOICE_DISPUTE_SUBMISSION_DEADLINE_KEY = 'nurse_invoice_dispute_submission_deadline';
    /**
     * @var Carbon
     */
    protected $invoiceMonth;

    /**
     * @var Carbon
     */
    private $deadline;

    public function __construct(Carbon $invoiceMonth)
    {
        $this->invoiceMonth = $invoiceMonth;
    }

    /**
     * Returns the deadline.
     *
     * @return Carbon
     */
    public function deadline()
    {
        if (isUnitTestingEnv()) {
            //throws exceptions it table does not exist
            //is a problem for CI
            //config is cached, so I chose this instead of checking if table exists
            //@todo: :shrug: fix
            return Carbon::now();
        }

        if ($this->deadline) {
            return $this->deadline->copy();
        }

        return \Cache::remember($this->getCacheKey(), self::CACHE_FOR_MINUTES, function () {
            $this->deadline = $this->calculateDeadline();

            return $this->deadline;
        });
    }

    /**
     * @return Carbon
     */
    public static function for(Carbon $invoiceMonth)
    {
        if (isUnitTestingEnv()) {
            //throws exceptions it table does not exist
            //is a problem for CI
            //config is cached, so I chose this instead of checking if table exists
            //@todo: :shrug: fix
            return Carbon::now();
        }

        return (new static($invoiceMonth))->deadline();
    }

    /**
     * Get the key used to store the deadline in the Cache.
     *
     * @return string
     */
    public function getCacheKey()
    {
        return 'nurse_dispute_deadline_for_'.$this->invoiceMonth->format('Y-m');
    }

    /**
     * Get warning message for preferredOrDefaultDeadline.
     *
     * @param Carbon $invoiceMonth
     *
     * @return string
     */
    public function warning()
    {
        $deadline = $this->deadline();

        return "Invoices auto-approve unless disputed by the {$deadline->format('jS')} of the month at {$deadline->format('h:iA T')}.";
    }

    /**
     * Gets the preferredOrDefaultDeadline from the app's config (set by admin from nova page).
     *
     * @return array
     */
    private function adminDefinedDeadline()
    {
        $deadline = explode(
            ' ',
            \CircleLinkHealth\Core\Entities\AppConfig::pull(
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
     * Calculates and returns the deadline.
     * If an admin has set a deadline it will validate and return that, otherwise it returns the default hardcoded value.
     *
     * @return Carbon
     */
    private function calculateDeadline()
    {
        $deadline = $this->preferredOrDefaultDeadline();

        $reviewMonth = $this->invoiceMonth->copy()->addMonth();

        //If the preferredOrDefaultDeadline is set to a day that does not exist in this month, use last day of month.
        //Example: preferredOrDefaultDeadline is set to 31st of the month, but month is April, which has 30 days
        if (($lastDayOfMonth = $reviewMonth->copy()->endOfMonth()->day) < $deadline['day']) {
            $deadline['day'] = $lastDayOfMonth;
        }

        return $reviewMonth->day($deadline['day'])->setTimeFromTimeString($deadline['time']);
    }

    /**
     * Gets the default preferredOrDefaultDeadline.
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
     * Returns the admin defined preferredOrDefaultDeadline to approve invoices. If the date was not set by the admin, it returns the
     * default preferredOrDefaultDeadline.
     *
     * @return array
     */
    private function preferredOrDefaultDeadline()
    {
        $deadline = $this->adminDefinedDeadline();

        $validator = $this->validator($deadline);

        if ($validator->passes()) {
            return $deadline;
        }

        return $this->defaultDeadline();
    }

    /**
     * Get a validator instance for the preferredOrDefaultDeadline.
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
