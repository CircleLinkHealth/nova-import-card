<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class AppConfig extends \App\BaseModel
{

    public $timestamps = true;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'app_config';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['config_key', 'config_value'];
}
