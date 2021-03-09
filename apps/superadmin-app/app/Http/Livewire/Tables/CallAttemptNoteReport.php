<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Livewire\Tables;

use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Entities\Call;
use CircleLinkHealth\SharedModels\Services\SchedulerService;
use Illuminate\Support\Facades\DB;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\DateColumn;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;

class CallAttemptNoteReport extends LivewireDatatable
{
    public function builder()
    {
        return Call::query()
            ->where(function ($q) {
                $q->whereNotNull('attempt_note')
                    ->where('attempt_note', '<>', '');
            })
            ->where('is_cpm_outbound', true)
            ->where('attempt_note', 'like', '%'.SchedulerService::EMAIL_SMS_RESPONSE_ATTEMPT_NOTE.'%')
            ->leftJoin(DB::raw('users as patients'), 'calls.inbound_cpm_id', 'patients.id')
            ->leftJoin(DB::raw('users as nurses'), 'calls.outbound_cpm_id', 'nurses.id')
            ->leftJoin(DB::raw('practices'), 'patients.program_id', 'practices.id')
            ->orderByDesc('created_at');
    }

    public function columns()
    {
        return [
            Column::checkbox(),

            Column::name('attempt_note')
                ->label('Attempt Note')
                ->searchable(),

            DateColumn::name('called_date')
                ->label('Date Called')
                ->filterable(),

            DateColumn::name('created_at')
                ->label('Date Created')
                ->filterable(),

            Column::name('nurses.display_name')
                ->label('Nurse')
                ->filterable($this->nurses())
                ->searchable(),

            Column::name('patients.display_name')
                ->callback(['patients.id', 'patients.display_name', 'calls.note_id'], function ($id, $name, $noteId) {
                      if ( ! $noteId) {
                          $url = route('patient.note.index', ['patientId' => $id]);
                      } else {
                          $url = route('patient.note.show', ['patientId' => $id, 'noteId' => $noteId]);
                      }

                      return '<a class="text-blue-500" target="_blank" href="'.$url.'">'.$name." [$id]".'</a>';
                  })
                ->label('Patient')
                ->filterable()
                ->searchable(),

            Column::name('practices.display_name')
                ->label('Practice')
                ->filterable($this->practices())
                ->searchable(),

            Column::name('id')
                ->label('ID')
                ->filterable()
                ->searchable(),
        ];
    }
    
    private function nurses()
    {
        return User::careCoaches()->without(['roles', 'perms'])->orderBy('display_name')->pluck('display_name');
    }
    
    private function practices()
    {
        return Practice::activeBillable()->orderBy('display_name')->pluck('display_name');
    }
}
