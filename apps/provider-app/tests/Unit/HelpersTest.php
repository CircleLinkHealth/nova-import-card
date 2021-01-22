<?php

/*
 * This file is part of CarePlan Manager by CircleLink Health.
 */

namespace Tests\Unit;

use CircleLinkHealth\Core\Tests\TestCase;
use CircleLinkHealth\Customer\Entities\User;
use Illuminate\Database\Eloquent\Collection;

class HelpersTest extends TestCase
{
    /**
     * Tests parseIds().
     */
    public function test_parse_ids()
    {
        //test model
        $model     = new User();
        $model->id = 1;
        $ids       = parseIds($model);
        $this->assertEquals([1], $ids);

        //test eloquent collection
        $eloquentCollection = new Collection();
        $eloquentCollection->push($model);
        $model2     = new User();
        $model2->id = 2;
        $eloquentCollection->push($model2);
        $ids = parseIds($eloquentCollection);
        $this->assertEquals([1, 2], $ids);

        //test collection of models
        $collection = new \Illuminate\Support\Collection();
        $collection->push($model2);
        $collection->push($model);
        $ids = parseIds($collection);
        $this->assertEquals([2, 1], $ids);

        //test collection of ids
        $collection = new \Illuminate\Support\Collection();
        $collection->push(5);
        $collection->push(6);
        $collection->push(7);
        $ids = parseIds($collection);
        $this->assertEquals([5, 6, 7], $ids);

        //test array of ids
        $arrayOfIds = [5, 6, 7];
        $ids        = parseIds($arrayOfIds);
        $this->assertEquals([5, 6, 7], $ids);

        //test array of objects
        $arrayOfObjects = [$model, $model2];
        $ids            = parseIds($arrayOfObjects);
        $this->assertEquals([1, 2], $ids);

        //test comma delimited string of id's
        $string = '1,2';
        $ids    = parseIds($string);
        $this->assertEquals([1, 2], $ids);

        //test string of single id
        $string = '1';
        $ids    = parseIds($string);
        $this->assertEquals([1], $ids);

        //test id int
        $int = 5;
        $ids = parseIds($int);
        $this->assertEquals([5], $ids);

        //test empty array
        $ids = parseIds([]);
        $this->assertEquals([], $ids);

        //test null
        $ids = parseIds(null);
        $this->assertEquals([], $ids);

        //test empty string
        $ids = parseIds('');
        $this->assertEquals([], $ids);
    }
}
