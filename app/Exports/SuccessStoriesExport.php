<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace App\Exports;

use CircleLinkHealth\SharedModels\Entities\Note;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SuccessStoriesExport implements FromCollection, WithHeadings
{
    /**
     * @var Carbon
     */
    private $month;

    /**
     * SuccessStoriesExport constructor.
     */
    public function __construct(Carbon $month)
    {
        $this->month = $month;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return Note::with('patient', 'author')
            ->where('success_story', '=', true)
            ->where('performed_at', '>=', $this->month->startOfMonth()->toDateString())
            ->where('performed_at', '<=', $this->month->endOfMonth()->toDateString())
            ->get()->map(function ($note) {
                // @var Note $note
                return [
                    $note->patient->display_name,
                    $note->author->display_name,
                    $note->patient->primaryPractice->display_name,
                    $note->type,
                    $note->link(),
                ];
            });
    }

    /**
     * {@inheritdoc}
     */
    public function headings(): array
    {
        return [
            'Patient Name',
            'Author Name',
            'Practice Name',
            'Note Type',
            'Note Link',
        ];
    }
}
