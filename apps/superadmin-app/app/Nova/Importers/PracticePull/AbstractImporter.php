<?php


namespace App\Nova\Importers\PracticePull;


use CircleLinkHealth\Customer\Entities\Media;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Events\AfterImport;

abstract class AbstractImporter  implements ToModel, WithChunkReading, WithHeadingRow, WithBatchInserts, ShouldQueue, WithEvents
{
    const FINISHED_PROCESSING_AT_LABEL = 'finishedProcessingAt';
    
    use Importable;
    
    protected int $batchId;
    protected int $mediaId;
    protected int $practiceId;
    
    /**
     * Medications constructor.
     */
    public function __construct(int $practiceId, int $batchId, int $mediaId)
    {
        $this->practiceId = $practiceId;
        $this->batchId = $batchId;
        $this->mediaId = $mediaId;
    }
    
    public function batchSize(): int
    {
        return 80;
    }
    
    public function chunkSize(): int
    {
        return 80;
    }
    
    /**
     * Returns null if value means N/A or equivalent. Otherwise returns the value passed to it.
     *
     * @param string $value
     *
     * @return string|null
     */
    public function nullOrValue($value)
    {
        return empty($value) || in_array($value, $this->nullValues())
            ? null
            : $value;
    }
    
    public function registerEvents(): array
    {
        return [
            AfterImport::class => function (AfterImport $event) {
                $this->markMediaAsDone();
                
                $this->clearDuplicates();
            },
        ];
    }
    
    protected function markMediaAsDone() {
        $media = Media::findOrFail($this->mediaId);
        $media->setCustomProperty(self::FINISHED_PROCESSING_AT_LABEL, now()->toDateTimeString());
        $media->save();
    }
    
    abstract function clearDuplicates();
}