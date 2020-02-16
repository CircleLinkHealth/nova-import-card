<?php

namespace CircleLinkHealth\Eligibility\Entities;

use CircleLinkHealth\Customer\Entities\Practice;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class PcmProblem extends Model
{
    use Searchable;
    
    protected $fillable = [
        'practice_id',
        'code_type',
        'code',
        'description',
    ];
    
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function practice()
    {
        return $this->belongsTo(Practice::class);
    }
    
    /**
     * Get Scout index name for the model.
     *
     * @return string
     */
    public function searchableAs()
    {
        return 'pcm_problems_index';
    }
    
    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray()
    {
        return [
            'practice_id' => $this->practice_id,
            'code_type'   => $this->code_type,
            'code'        => $this->code,
            'description' => $this->description,
        ];
    }
    
    /**
     * Get the value used to index the model.
     *
     * @return mixed
     */
    public function getScoutKey()
    {
        return $this->id;
    }
}
