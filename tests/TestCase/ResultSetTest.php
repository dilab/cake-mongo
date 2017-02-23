<?php

namespace Dilab\CakeMongo;


use Cake\TestSuite\TestCase;


class ResultSetTest extends TestCase
{
    public $fixtures = ['plugin.dilab/cake_mongo.articles'];

    public function setUp()
    {
        parent::setUp();
    }

    public function tearDown()
    {
        parent::tearDown();
    }

    public function testConstructor()
    {
        $cursor = $this->getMockBuilder('\Traversable')
            ->disableOriginalConstructor()
            ->getMock();

        $collection = $this->getMockBuilder('Dilab\CakeMongo\Collection')
            ->disableOriginalConstructor()
            ->getMock();

        $query = $this->getMockBuilder('Dilab\CakeMongo\Query')
            ->disableOriginalConstructor()
            ->setMethods(['repository'])
            ->getMock();

        $query->expects($this->any())->method('repository')
            ->will($this->returnValue($collection));

        return [new ResultSet($cursor, $query), $cursor];
    }

    /**
     * Tests that calling current will wrap the result using the provided entity
     * class
     *
     * @depends testConstructor
     * @return void
     */
    public function testCurrent($resultSets)
    {
        $this->markTestSkipped();
        list($resultSet, $cursor) = $resultSets;
        $data = ['foo' => 1, 'bar' => 2];
        $result = $this->getMock('Elastica\Result', ['getId', 'getData', 'getType'], [[]]);
        $result->method('getData')
            ->will($this->returnValue($data));
        $result->method('getId')
            ->will($this->returnValue(99));
        $result->method('getType')
            ->will($this->returnValue('things'));
        $cursor->expects($this->once())
            ->method('current')
            ->will($this->returnValue($result));
        $document = $resultSet->current();
        $this->assertInstanceOf(__NAMESPACE__ . '\MyTestDocument', $document);
        $this->assertSame($data + ['id' => 99], $document->toArray());
        $this->assertFalse($document->dirty());
        $this->assertFalse($document->isNew());
    }
}
