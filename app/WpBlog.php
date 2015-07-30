<?php namespace App;

use Illuminate\Database\Eloquent\Model;

class WpBlog extends Model {

    /**
     * The connection name for the model.
     *
     * @var string
     */
    protected $connection = 'mysql_no_prefix';

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'wp_blogs';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'blog_id';

	//

    public function locationId() {
        $location = \DB::select("select * from wp_".$this->blog_id."_options where option_name = 'location_id'", []);
        if(isset($location[0])) {
            return $location[0]->option_value;
        } else {
            return false;
        }
    }

}
