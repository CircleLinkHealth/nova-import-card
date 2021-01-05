<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Livewire\Tables;

use CircleLinkHealth\Customer\Entities\Practice;
use CircleLinkHealth\Customer\Entities\User;
use CircleLinkHealth\SharedModels\Entities\Note;
use Illuminate\Support\Facades\DB;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\DateColumn;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;
use Mediconesystems\LivewireDatatables\NumberColumn;

class HospitalisationNotesReport extends LivewireDatatable
{
    public function builder()
    {
        return Note::query()
            ->where('isTCM', true)
            ->leftJoin(DB::raw('users as patients'), 'notes.patient_id', 'patients.id')
            ->leftJoin(DB::raw('users as nurses'), 'notes.author_id', 'nurses.id')
            ->leftJoin(DB::raw('practices'), 'patients.program_id', 'practices.id');
    }

    public function columns()
    {
        return [
            Column::checkbox(),

            NumberColumn::name('id')
                        ->label('Note ID')
                        ->filterable()
                        ->linkTo('note', 6),

            Column::name('nurses.display_name')
                ->label('Nurse')
                ->filterable($this->nurses())
                ->searchable(),

            Column::name('patients.display_name')
                ->label('Patient')
                ->filterable()
                ->searchable(),

            Column::name('practices.display_name')
                  ->label('Practice')
                  ->filterable($this->practices())
                  ->searchable(),

            Column::name('body')
                ->label('Note')
                ->filterable()
                ->searchable()
                ->truncate(100),

            DateColumn::name('performed_at')
                      ->label('Date')
                      ->filterable(),
        ];
    }

    private function nurses()
    {
        return User::careCoaches()->activeNurses()->without(['roles', 'perms'])->orderBy('display_name')->pluck('display_name');
    }
    
    private function practices()
    {
        return Practice::activeBillable()->orderBy('display_name')->pluck('display_name');
    }
}
