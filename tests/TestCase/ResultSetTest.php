<?php

namespace Dilab\CakeMongo;


use Cake\Datasource\ConnectionManager;
use Cake\TestSuite\TestCase;
use Dilab\CakeMongo\Datasource\Connection;


class ResultSetTest extends TestCase
{
    public $fixtures = ['plugin.dilab/cake_mongo.articles'];

    public function setUp()
    {
        parent::setUp();
    }

    public function tearDown()
    {
//        parent::tearDown();
    }

    public function testConstructor()
    {
        $connection = new Connection();

        $database = $connection->getDatabase();

        $collection = $database->selectCollection('articles');

        $query = (new Query(new Collection(['name' => 'articles'])))->where(['title' => 'First article']);

        $cursor = $collection->find(['title' => 'First article']);

        return new ResultSet($cursor, $query);
    }

    /**
     * Tests that calling current will wrap the result using the provided entity
     * class
     *
     * @depends testConstructor
     * @return void
     */
    public function testCurrent($resultSet)
    {
        $document = $resultSet->current();
        var_dump($document);
        $this->assertInstanceOf(Document::class, $document);
//        $this->assertSame([
//            'id' => '1',
//            'title' => 'First article',
//            'user_id' => 1,
//            'body' => 'A big box of bolts and nuts.',
//            'created' => '2014-04-01T15:01:30',
//        ], $document->toArray());
//        $this->assertFalse($document->dirty());
//        $this->assertFalse($document->isNew());
    }
}
