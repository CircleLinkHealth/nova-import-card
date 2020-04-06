<?php

namespace App\Exports;

use App\Note;
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
     * @param Carbon $month
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
                return [
                    $note->patient->display_name,
                    $note->author->display_name,
                    $note->type
                ];
            });
    }

    /**
     * @inheritDoc
     */
    public function headings(): array
    {
        return [
            'Patient Name',
            'Author Name',
            'Note Type'
        ];
    }
}
