<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 5/5/16
 * Time: 2:54 PM
 */

namespace App\Contracts;

interface Serviceable
{
    /**
     * Get this Model's Service Class
     *
     * @return Serviceable
     */
    public function service();
}
