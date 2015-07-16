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

}
