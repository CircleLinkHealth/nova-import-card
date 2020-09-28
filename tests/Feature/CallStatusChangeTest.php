<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Feature;

use App\Call;
use App\Jobs\CreateNurseInvoices;
use App\Traits\Tests\PracticeHelpers;
use App\Traits\Tests\TimeHelpers;
use CircleLinkHealth\Customer\Traits\UserHelpers;
use CircleLinkHealth\Customer\Entities\PatientMonthlySummary;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\NurseInvoices\Entities\NurseInvoice;
use Tests\CustomerTestCase;

class CallStatusChangeTest extends CustomerTestCase
{
    use PracticeHelpers;
    use TimeHelpers;
    use UserHelpers;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_create_note_with_successful_call_change_to_unsuccessful()
    {
        $practice = $this->practice();
        $this->setupExistingPractice($practice, true);

        /** @var User $patient */
        $patient = $this->patient();

        /** @var User $nurse */
        $nurse = $this->careCoach();
        $this->setupNurse($nurse, true, 30, true, 12.50);

        $note = $this->addTime($nurse, $patient, 20, true, true, false, null, 0, 'Patient Note Creation', false, true);

        $invoice         = $this->generateInvoice($nurse);
        $invoiceData     = $invoice->invoice_data;
        $fixedRatePay    = $invoiceData['fixedRatePay'];
        $variableRatePay = $invoiceData['variableRatePay'];
        $pay             = $invoiceData['baseSalary'];

        self::assertEquals(15.00, $fixedRatePay);
        self::assertEquals(12.50, $variableRatePay);
        self::assertEquals(15.00, $pay);

        /** @var PatientMonthlySummary $pms */
        $pms = PatientMonthlySummary::where('month_year', '=', now()->startOfMonth()->toDateString())
            ->where('patient_id', '=', $patient->id)
            ->first();

        self::assertEquals(1, $pms->no_of_calls);
        self::assertEquals(1, $pms->no_of_successful_calls);

        /** @var User $admin */
        $admin = $this->superadmin();
        $this->be($admin);
        $this->patch(route('CallsDashboard.edit', [
            'callId'  => $note->call->id,
            'noteId'  => $note->id,
            'nurseId' => $nurse->id,
            'status'  => Call::NOT_REACHED,
        ]));

        $invoice         = $this->generateInvoice($nurse);
        $invoiceData     = $invoice->invoice_data;
        $fixedRatePay    = $invoiceData['fixedRatePay'];
        $variableRatePay = $invoiceData['variableRatePay'];
        $pay             = $invoiceData['baseSalary'];

        self::assertEquals(15.00, $fixedRatePay);
        self::assertEquals(0.00, $variableRatePay);
        self::assertEquals(15.00, $pay);

        $pms = $pms->fresh();
        self::assertEquals(1, $pms->no_of_calls);
        self::assertEquals(0, $pms->no_of_successful_calls);
    }

    public function test_create_note_with_unsuccessful_call_change_to_successful()
    {
        $practice = $this->practice();
        $this->setupExistingPractice($practice, true);

        /** @var User $patient */
        $patient = $this->patient();

        /** @var User $nurse */
        $nurse = $this->careCoach();
        $this->setupNurse($nurse, true, 30, true, 12.50);

        $note = $this->addTime($nurse, $patient, 20, true, false, false, null, 0, 'Patient Note Creation', false, true);

        $invoice         = $this->generateInvoice($nurse);
        $invoiceData     = $invoice->invoice_data;
        $fixedRatePay    = $invoiceData['fixedRatePay'];
        $variableRatePay = $invoiceData['variableRatePay'];
        $pay             = $invoiceData['baseSalary'];

        self::assertEquals(15.00, $fixedRatePay);
        self::assertEquals(0.00, $variableRatePay);
        self::assertEquals(15.00, $pay);

        /** @var PatientMonthlySummary $pms */
        $pms = PatientMonthlySummary::where('month_year', '=', now()->startOfMonth()->toDateString())
            ->where('patient_id', '=', $patient->id)
            ->first();

        self::assertEquals(1, $pms->no_of_calls);
        self::assertEquals(0, $pms->no_of_successful_calls);

        /** @var User $admin */
        $admin = $this->superadmin();
        $this->be($admin);
        $this->patch(route('CallsDashboard.edit', [
            'callId'  => $note->call->id,
            'noteId'  => $note->id,
            'nurseId' => $nurse->id,
            'status'  => Call::REACHED,
        ]));

        $invoice         = $this->generateInvoice($nurse);
        $invoiceData     = $invoice->invoice_data;
        $fixedRatePay    = $invoiceData['fixedRatePay'];
        $variableRatePay = $invoiceData['variableRatePay'];
        $pay             = $invoiceData['baseSalary'];

        self::assertEquals(15.00, $fixedRatePay);
        self::assertEquals(12.50, $variableRatePay);
        self::assertEquals(15.00, $pay);

        $pms = $pms->fresh();
        self::assertEquals(1, $pms->no_of_calls);
        self::assertEquals(1, $pms->no_of_successful_calls);
    }

    public function test_create_note_without_call_change_to_successful()
    {
        $practice = $this->practice();
        $this->setupExistingPractice($practice, true);

        /** @var User $patient */
        $patient = $this->patient();

        /** @var User $nurse */
        $nurse = $this->careCoach();
        $this->setupNurse($nurse, true, 30, true, 12.50);

        $note = $this->addTime($nurse, $patient, 20, true, false, false, null, 0, 'Patient Note Creation', false, false);

        $invoice         = $this->generateInvoice($nurse);
        $invoiceData     = $invoice->invoice_data;
        $fixedRatePay    = $invoiceData['fixedRatePay'];
        $variableRatePay = $invoiceData['variableRatePay'];
        $pay             = $invoiceData['baseSalary'];

        self::assertEquals(15.00, $fixedRatePay);
        self::assertEquals(0.00, $variableRatePay);
        self::assertEquals(15.00, $pay);

        /** @var PatientMonthlySummary $pms */
        $pms = PatientMonthlySummary::where('month_year', '=', now()->startOfMonth()->toDateString())
            ->where('patient_id', '=', $patient->id)
            ->first();

        self::assertEquals(0, $pms->no_of_calls);
        self::assertEquals(0, $pms->no_of_successful_calls);

        /** @var User $admin */
        $admin = $this->superadmin();
        $this->be($admin);
        $this->post(route('CallsDashboard.create-call', [
            'noteId'    => $note->id,
            'nurseId'   => $nurse->id,
            'status'    => Call::REACHED,
            'direction' => 'outbound',
        ]));

        $invoice         = $this->generateInvoice($nurse);
        $invoiceData     = $invoice->invoice_data;
        $fixedRatePay    = $invoiceData['fixedRatePay'];
        $variableRatePay = $invoiceData['variableRatePay'];
        $pay             = $invoiceData['baseSalary'];

        self::assertEquals(15.00, $fixedRatePay);
        self::assertEquals(12.50, $variableRatePay);
        self::assertEquals(15.00, $pay);

        $pms = $pms->fresh();
        self::assertEquals(1, $pms->no_of_calls);
        self::assertEquals(1, $pms->no_of_successful_calls);
    }

    public function test_create_note_without_call_change_to_unsuccessful()
    {
        $practice = $this->practice();
        $this->setupExistingPractice($practice, true);

        /** @var User $patient */
        $patient = $this->patient();

        /** @var User $nurse */
        $nurse = $this->careCoach();
        $this->setupNurse($nurse, true, 30, true, 12.50);

        $note = $this->addTime($nurse, $patient, 20, true, false, false, null, 0, 'Patient Note Creation', false, false);

        $invoice         = $this->generateInvoice($nurse);
        $invoiceData     = $invoice->invoice_data;
        $fixedRatePay    = $invoiceData['fixedRatePay'];
        $variableRatePay = $invoiceData['variableRatePay'];
        $pay             = $invoiceData['baseSalary'];

        self::assertEquals(15.00, $fixedRatePay);
        self::assertEquals(0.00, $variableRatePay);
        self::assertEquals(15.00, $pay);

        /** @var PatientMonthlySummary $pms */
        $pms = PatientMonthlySummary::where('month_year', '=', now()->startOfMonth()->toDateString())
            ->where('patient_id', '=', $patient->id)
            ->first();

        self::assertEquals(0, $pms->no_of_calls);
        self::assertEquals(0, $pms->no_of_successful_calls);

        /** @var User $admin */
        $admin = $this->superadmin();
        $this->be($admin);
        $this->post(route('CallsDashboard.create-call', [
            'noteId'    => $note->id,
            'nurseId'   => $nurse->id,
            'status'    => Call::NOT_REACHED,
            'direction' => 'outbound',
        ]));

        $invoice         = $this->generateInvoice($nurse);
        $invoiceData     = $invoice->invoice_data;
        $fixedRatePay    = $invoiceData['fixedRatePay'];
        $variableRatePay = $invoiceData['variableRatePay'];
        $pay             = $invoiceData['baseSalary'];

        self::assertEquals(15.00, $fixedRatePay);
        self::assertEquals(0.00, $variableRatePay);
        self::assertEquals(15.00, $pay);

        self::assertEquals(1, $pms->fresh()->no_of_calls);
        self::assertEquals(0, $pms->fresh()->no_of_successful_calls);
    }

    private function generateInvoice(User $nurse): ?NurseInvoice
    {
        $start = now()->startOfMonth();
        $end   = now()->endOfMonth();

        (new CreateNurseInvoices(
            $start,
            $end,
            [$nurse->id],
            false,
            null,
            true
        ))->handle();

        // @var NurseInvoice $invoice
        return NurseInvoice::where('nurse_info_id', $nurse->nurseInfo->id)
            ->orderBy('month_year', 'desc')
            ->first();
    }
}
