<?php

namespace Dilab\CakeMongo;


use Cake\TestSuite\TestCase;

/**
 * Tests the Query class
 *
 */
class QueryTest extends TestCase
{
    /**
     * Tests query constructor
     *
     * @return void
     */
    public function testConstruct()
    {
        $type = new Collection();
        $query = new Query($type);
        $this->assertSame($type, $query->repository());
    }

}
