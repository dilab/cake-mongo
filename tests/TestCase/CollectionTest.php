<?php


namespace Dilab\CakeMongo;


use Cake\Datasource\ConnectionManager;
use Cake\TestSuite\TestCase;

class CollectionTest extends TestCase
{
    /**
     * @var Collection
     */
    public $collection;

//    public $fixtures = ['plugin.cake/cake_mongo.articles'];

    public function setUp()
    {
        parent::setUp();
        $this->collection = new Collection([
            'name' => 'articles',
            'connection' => ConnectionManager::get('test')
        ]);
    }

    /**
     * Tests that calling find will return a query object
     *
     * @return void
     */
    public function testFindAll()
    {
        $query = $this->collection->find('all');
        $this->assertInstanceOf('Dilab\CakeMongo\Query', $query);
        $this->assertSame($this->collection, $query->repository());
    }
}
