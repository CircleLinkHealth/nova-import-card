<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class XmlCCD extends Model {

	protected $table = 'xml_ccds';

    protected $guarded = [];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(WpUser::class);
    }

}
