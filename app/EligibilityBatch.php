<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class EligibilityBatch extends Model
{
    const TYPE_GOOGLE_DRIVE = 'google_drive';

    const STATUSES = [
        'not_started' => 0,
        'processing'  => 1,
        'error'       => 2,
        'complete'    => 3,
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'options' => 'array',
        'stats'   => 'array',
    ];

    protected $fillable = [
        'type',
        'options',
        'stats',
        'status',
    ];

    protected $attributes = [
        'status' => 0,
    ];

    public function incrementEligibleCount()
    {
        $this->incrementCount('eligible');
    }

    public function incrementIneligibleCount()
    {
        $this->incrementCount('ineligible');
    }

    public function incrementErrorCount()
    {
        $this->incrementCount('errors');
    }

    public function incrementDuplicateCount()
    {
        $this->incrementCount('duplicates');
    }

    private function incrementCount($key) {
        $stats = $this->stats;

        $stats[$key] = $this->stats[$key] + 1;

        $this->stats = $stats;
    }

    public function getStatus($statusId = null) {
        if (!$statusId) {
            if (!$this->status) {
                return null;
            }
            $statusId = $this->status;
        }


        foreach (self::STATUSES as $name => $id) {
            if ($id == $statusId) {
                return $name;
            }
        }

        return null;
    }
}
