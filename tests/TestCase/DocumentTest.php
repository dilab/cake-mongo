<?php


namespace Dilab\CakeMongo;


use Cake\TestSuite\TestCase;
use MongoDB\Model\BSONDocument;

class DocumentTest extends TestCase
{
    /**
     * Tests constructing a document
     *
     * @return void
     */
    public function testConstructorArray()
    {
        $data = ['foo' => 1, 'bar' => 2];
        $document = new Document($data);
        $this->assertSame($data, $document->toArray());
    }

    /**
     * Tests that constructing a document with a BSONDocument will
     * use the returned data out of it
     *
     * @return void
     */
    public function testConstructorWithResult()
    {
        $data = ['foo' => 1, 'bar' => 2];
        $result = new BSONDocument($data);
        $document = new Document($result);
        $this->assertSame($data, $document->toArray());
    }

    /**
     * Tests that the BSONDocument object can be passed in the options array
     *
     * @return void
     */
    public function testConstructorWithResultAsOption()
    {
        $data = ['foo' => 1, 'bar' => 2];
        $result = new BSONDocument($data);
        $document = new Document($data, ['result' => $result]);
        $this->assertSame($data, $document->toArray());
    }

    /**
     * Tests that creating a document without a result object will
     * make the proxy functions return their default
     *
     * @return void
     */
    public function testNewWithNoResult()
    {
        $this->markTestSkipped('Wait until Mongo specific attributes are needed');
        $document = new Document();
        $this->assertNull($document->col());
        $this->assertSame(1, $document->version());
        $this->assertEquals([], $document->highlights());
        $this->assertEquals([], $document->explanation());
    }

    /**
     * Tests that passing a result object in the constructor makes
     * the proxy the functions return the right value
     *
     * @return void
     */
    public function testTypeWithResult()
    {
        $this->markTestSkipped('Wait until Mongo specific attributes are needed');

        $result = $this->getMock('Elastica\Result', [], [[]]);
        $data = ['a' => 'b'];

        $result
            ->method('getData')
            ->will($this->returnValue($data));

        $result
            ->method('getId')
            ->will($this->returnValue(1));

        $result
            ->method('getType')
            ->will($this->returnValue('things'));

        $result
            ->method('getVersion')
            ->will($this->returnValue(3));

        $result
            ->method('getHighlights')
            ->will($this->returnValue(['highlights array']));

        $result
            ->method('getExplanation')
            ->will($this->returnValue(['explanation array']));

        $document = new Document($result);
        $this->assertSame($data + ['id' => 1], $document->toArray());
        $this->assertEquals('things', $document->type());
        $this->assertEquals(3, $document->version());
        $this->assertEquals(['highlights array'], $document->highlights());
        $this->assertEquals(['explanation array'], $document->explanation());
    }

}
