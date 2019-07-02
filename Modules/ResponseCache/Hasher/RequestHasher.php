<?php
/**
 * Created by PhpStorm.
 * User: michalis
 * Date: 7/2/19
 * Time: 3:44 PM
 */

namespace CircleLinkHealth\ResponseCache\Hasher;

use Illuminate\Http\Request;

interface RequestHasher
{
    public function getHashFor(Request $request): string;
}
