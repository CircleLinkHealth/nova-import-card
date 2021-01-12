<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace CircleLinkHealth\Core\Exports;

use CircleLinkHealth\Core\Traits\AttachableAsMedia;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class FromArray implements FromCollection, Responsable, WithHeadings
{
    use AttachableAsMedia;
    use Exportable;
    /**
     * @var array
     */
    protected $columnTitles;
    /**
     * @var array
     */
    protected $reportData;
    /**
     * @var string
     */
    private $filename;

    /**
     * FromArray constructor.
     *
     * @param $filename
     */
    public function __construct($filename, array $reportData, array $columnTitles = [])
    {
        $this->setFilename($filename);
        $this->filename     = $filename;
        $this->reportData   = $reportData;
        $this->columnTitles = $columnTitles;
    }

    /**
     * @return Collection
     */
    public function collection()
    {
        return collect($this->reportData);
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function headings(): array
    {
        return ! empty($this->columnTitles)
            ? $this->columnTitles
            : $this->headingsFromReportData();
    }

    public function setFilename(string $filename): FromArray
    {
        $this->filename = $filename;

        return $this;
    }

    public function storeAndAttachMediaTo($model, $mediaCollection)
    {
        $disk = Storage::disk('storage');
        $disk->makeDirectory('exports');

        $filepath = 'exports/'.$this->getFilename();
        $stored   = $this->store($filepath, 'storage');

        return $this->attachMediaTo($model, $disk->path($filepath), $mediaCollection);
    }

    private function headingsFromReportData()
    {
        $collection = $this->collection();
        $firstItem  = $collection->first();

        if (is_array($firstItem)) {
            return array_keys($firstItem);
        }

        return $collection->keys()->all();
    }
}
