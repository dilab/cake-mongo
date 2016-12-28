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
        $connection = $this->getMock(
            'Cake\ElasticSearch\Datasource\Connection',
            ['getIndex']
        );
        $type = new Type([
            'name' => 'foo',
            'connection' => $connection
        ]);

        $index = $this->getMockBuilder('Elastica\Index')
            ->disableOriginalConstructor()
            ->getMock();

        $internalType = $this->getMockBuilder('Elastica\Type')
            ->disableOriginalConstructor()
            ->getMock();

        $connection->expects($this->once())
            ->method('getIndex')
            ->will($this->returnValue($index));

        $index->expects($this->once())
            ->method('getType')
            ->will($this->returnValue($internalType));

        $result = $this->getMockBuilder('Elastica\ResultSet')
            ->disableOriginalConstructor()
            ->getMock();

        $internalQuery = $this->getMockBuilder('Elastica\Query')
            ->disableOriginalConstructor()
            ->getMock();

        $internalType->expects($this->once())
            ->method('search')
            ->will($this->returnCallback(function ($query) use ($result) {
                $this->assertEquals(new \Elastica\Query, $query);

                return $result;
            }));

        $query = new Query($type);
        $resultSet = $query->all();
        $this->assertInstanceOf('Cake\ElasticSearch\ResultSet', $resultSet);
        $this->assertSame($result, $resultSet->getInnerIterator());
    }

    /**
     * Tests that calling select() sets the field to select from _source
     *
     * @return void
     */
    public function testSelect()
    {
        $type = new Collection();
        $query = new Query($type);
        $this->assertSame($query, $query->select(['a', 'b']));
        $mongoQuery = $query->compileQuery();
        $this->assertEquals(['a' => 1, 'b' => 1], $mongoQuery['projection']);

        $query->select(['c', 'd']);
        $elasticQuery = $query->compileQuery();
        $this->assertEquals(['a' => 1, 'b' => 1, 'c' => 1, 'd' => 1], $elasticQuery['projection']);

        $query->select(['e', 'f'], true);
        $elasticQuery = $query->compileQuery();
        $this->assertEquals(['e' => 1, 'f' => 1], $elasticQuery['projection']);
    }


}
