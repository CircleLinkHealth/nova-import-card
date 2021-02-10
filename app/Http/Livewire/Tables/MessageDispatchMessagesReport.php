<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Http\Livewire\Tables;

use CircleLinkHealth\SharedModels\Entities\PostmarkInboundMail;
use Mediconesystems\LivewireDatatables\Column;
use Mediconesystems\LivewireDatatables\DateColumn;
use Mediconesystems\LivewireDatatables\Http\Livewire\LivewireDatatable;

class MessageDispatchMessagesReport extends LivewireDatatable
{
    public function builder()
    {
        return PostmarkInboundMail::query()
            ->where('body', 'like', '%From: Message Dispatch%')
            ->orderByDesc('created_at');
    }

    public function columns()
    {
        return [
            Column::checkbox(),

            Column::name('body')
                  ->label('Message')
                  ->searchable(),

            DateColumn::name('created_at')
                ->label('Date Received')
                ->filterable(),

            Column::name('id')
                  ->label('ID')
                  ->searchable(),
        ];
    }
}
