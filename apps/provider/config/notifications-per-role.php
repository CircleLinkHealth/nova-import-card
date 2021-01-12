<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

use App\Notifications\AddendumCreated;
use App\Notifications\CarePlanApprovalReminder;
use App\Notifications\InvoiceReminder;
use CircleLinkHealth\NurseInvoices\Notifications\DisputeResolved;
use CircleLinkHealth\NurseInvoices\Notifications\InvoiceBeforePayment;
use CircleLinkHealth\NurseInvoices\Notifications\InvoiceReviewInitialReminder;

return [
    'care-center' => [
        'email' => [
            AddendumCreated::class              => 'Note',
            CarePlanApprovalReminder::class     => 'CP Approval Reminders',
            InvoiceReminder::class              => 'Invoice Reminders',
            DisputeResolved::class              => 'Invoice Disputes',
            InvoiceBeforePayment::class         => 'Before Invoice Email',
            InvoiceReviewInitialReminder::class => 'Invoice Review',
        ],
        'sms' => [
        ],

        'live_notifications' => [
        ],
    ],

    'all' => [
        'email' => [
        ],
    ],
];
