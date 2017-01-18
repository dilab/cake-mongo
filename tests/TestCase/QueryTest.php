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
        $collection = new Collection();
        $query = new Query($collection);
        $this->assertSame($collection, $query->repository());
    }

    /**
     * Test that chained finders will work
     *
     * @return void
     */
    public function testChainedFinders()
    {
        $this->markTestIncomplete();
        $collection = new Collection();
        $query = new Query($collection);
        $this->assertInstanceOf(Query::class, $query->find()->find());
    }

    /**
     * Tests that executing a query means executing a search against the associated
     * Collection and decorates the internal ResultSet
     *
     * @return void
     */
    public function testAll()
    {
        $this->markTestIncomplete();
    }

    /**
     * Tests that calling select() sets the field to select from _source
     *
     * @return void
     */
    public function testSelect()
    {
        $collection = new Collection();
        $query = new Query($collection);
        $this->assertSame($query, $query->select(['a', 'b']));
        $mongoQuery = $query->compileQuery();
        $this->assertEquals(['a' => 1, 'b' => 1], $mongoQuery['projection']);

        $query->select(['c', 'd']);
        $mongoQuery = $query->compileQuery();
        $this->assertEquals(['a' => 1, 'b' => 1, 'c' => 1, 'd' => 1], $mongoQuery['projection']);

        $query->select(['e', 'f'], true);
        $mongoQuery = $query->compileQuery();
        $this->assertEquals(['e' => 1, 'f' => 1], $mongoQuery['projection']);
    }

    /**
     * Tests that calling limit() sets the limit option for the MongoDB query
     *
     * @return void
     */
    public function testLimit()
    {
        $collection = new Collection();
        $query = new Query($collection);
        $this->assertSame($query, $query->limit(10));

        $mongoQuery = $query->compileQuery();

        $this->assertSame(10, $mongoQuery['limit']);

        $this->assertSame($query, $query->limit(20));
        $mongoQuery = $query->compileQuery();
        $this->assertSame(20, $mongoQuery['limit']);

    }

    /**
     * Tests that calling offset() sets the from option for the MongoDB query
     *
     * @return void
     */
    public function testOffset()
    {
        $collection = new Collection();
        $query = new Query($collection);

        $this->assertSame($query, $query->offset(10));
        $mongoQuery = $query->compileQuery();
        $this->assertSame(10, $mongoQuery['skip']);

        $this->assertSame($query, $query->offset(20));
        $mongoQuery = $query->compileQuery();
        $this->assertSame(20, $mongoQuery['skip']);
    }

    /**
     * Tests that calling order() will populate the sort part of the MongoDB query.
     *
     * @return void
     */
    public function testOrder()
    {
        $collection = new Collection();
        $query = new Query($collection);
        $this->assertSame($query, $query->order('price'));

        $mongoQuery = $query->compileQuery();
        $expected = ['price' => -1];
        $this->assertEquals($expected, $mongoQuery['sort']);

        $query->order(['created' => 'asc']);
        $mongoQuery = $query->compileQuery();
        $expected = ['price' => -1, 'created' => 1];
        $this->assertEquals($expected, $mongoQuery['sort']);

        $query->order(['modified' => 'desc', 'score' => 'asc']);
        $mongoQuery = $query->compileQuery();
        $expected = ['price' => -1, 'created' => 1, 'modified' => -1, 'score' => 1];
        $this->assertEquals($expected, $mongoQuery['sort']);

        $query->order(['created' => 'asc'], true);
        $mongoQuery = $query->compileQuery();
        $expected = ['created' => 1];
        $this->assertEquals($expected, $mongoQuery['sort']);
    }

    /**
     * Tests that calling clause() gets the part of the query
     *
     * @return void
     */
    public function testClause()
    {
        $collection = new Collection();
        $query = new Query($collection);

        $query->page(10);
        $this->assertSame(25, $query->clause('limit'));
        $this->assertSame(225, $query->clause('offset'));

        $query->limit(12);
        $this->assertSame(12, $query->clause('limit'));

        $query->offset(100);
        $this->assertSame(100, $query->clause('offset'));

        $query->order('price');
        $this->assertSame([0 => [
            'price' => [
                'order' => 'desc'
            ]
        ]], $query->clause('order'));
    }

    /**
     * Tests that calling page() sets the skip option for the MongoDB query and limit (optional)
     *
     * @return void
     */
    public function testPage()
    {
        $collection = new Collection();
        $query = new Query($collection);
        $this->assertSame($query, $query->page(10));
        $mongoQuery = $query->compileQuery();
        $this->assertSame(225, $mongoQuery['skip']);
        $this->assertSame(25, $mongoQuery['limit']);

        $this->assertSame($query, $query->page(20, 50));
        $mongoQuery = $query->compileQuery();
        $this->assertSame(950, $mongoQuery['skip']);
        $this->assertSame(50, $mongoQuery['limit']);

        $query->limit(15);
        $this->assertSame($query, $query->page(20));
        $mongoQuery = $query->compileQuery();
        $this->assertSame(285, $mongoQuery['skip']);
        $this->assertSame(15, $mongoQuery['limit']);
    }

    public function testWhere()
    {

    }

    /**
     * Tests that calling applyOptions() sets parts of the query
     *
     * @return void
     */
    public function testApplyOptions()
    {
        $this->markTestIncomplete();
        $collection = new Collection();
        $query = new Query($collection);

        $query->applyOptions([
            'fields' => ['id', 'name'],
            'conditions' => [
                'created >=' => '2013-01-01'
            ],
            'limit' => 10
        ]);

        $result = [
            'projection' => ['id'=>1, 'name'=>1],
            'limit' => 10,
            'query' => [
                'filtered' => [
                    'filter' => [
                        'bool' => [
                            'must' => [[
                                'range' => [
                                    'created' => [
                                        'gte' => '2013-01-01'
                                    ]
                                ]
                            ]]
                        ]
                    ]
                ]
            ]
        ];

        $this->assertSame($result, $query->compileQuery());
    }
}
