<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EligibilityJob extends Model
{
    use SoftDeletes;

    const ELIGIBLE = 'eligible';
    const INELIGIBLE = 'ineligible';
    const DUPLICATE = 'duplicate';

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
        'data'     => 'array',
        'messages' => 'array',
    ];

    protected $fillable = [
        'batch_id',
        'hash',
        'data',
        'messages',
        'outcome',
        'status',
    ];

    protected $attributes = [
        'status' => 0,
    ];

    public function getStatus($statusId = null)
    {
        if ( ! $statusId) {
            if (is_null($this->status)) {
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
