<?php


namespace Dilab\CakeMongo;


use Cake\TestSuite\TestCase;

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

}
