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

    public $fixtures = ['plugin.dilab/cake_mongo.articles'];

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

    /**
     * Test the default entityClass.
     *
     * @return void
     */
    public function testEntityClassDefault()
    {
        $this->assertEquals('\Dilab\CakeMongo\Document', $this->collection->entityClass());
    }

    /**
     * Tests that calling find will return a query object
     *
     * @expectedException \Cake\Datasource\Exception\RecordNotFoundException
     * @return void
     */
    public function testFindAllWithFirstOrFail()
    {
        $this->collection->find('all')->where(['id' => '999999999'])->firstOrFail();
    }

    /**
     * Tests that table() is implemented as QueryTrait relies on.
     *
     * @return void
     */
    public function testTable()
    {
        $this->assertSame('articles', $this->collection->table());
    }

    /**
     * Tests that using a simple string for entityClass will try to
     * load the class from the App namespace
     *
     * @return void
     */
    public function testTableClassInApp()
    {
        $class = $this->getMockClass('\Dilab\CakeMongo\Document');
        class_alias($class, 'App\Model\Document\TestUser');

        $collection = new Collection();
        $this->assertEquals(
            'App\Model\Document\TestUser',
            $collection->entityClass('TestUser')
        );
    }

    /**
     * Tests that using a simple string for entityClass will try to
     * load the class from the Plugin namespace when using plugin notation
     *
     * @return void
     */
    public function testTableClassInPlugin()
    {
        $class = $this->getMockClass('\Dilab\Mongo\Document');
        class_alias($class, 'MyPlugin\Model\Document\SuperUser');

        $collection = new Collection();
        $this->assertEquals(
            'MyPlugin\Model\Document\SuperUser',
            $collection->entityClass('MyPlugin.SuperUser')
        );
    }

    /**
     * Tests the get method
     *
     * @return void
     */
    public function testGet()
    {
        $result = $this->collection->get('507f191e810c19729de860ea');
        $this->assertInstanceOf('Dilab\CakeMongo\Document', $result);
        $this->assertEquals([
            'title' => 'First article',
            'user_id' => 1,
            'body' => 'A big box of bolts and nuts.',
            'created' => '2014-04-01T15:01:30',
            '_id' => '507f191e810c19729de860ea'
        ], $result->toArray());
        $this->assertFalse($result->dirty());
        $this->assertFalse($result->isNew());
    }
}
