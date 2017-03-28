<?php


namespace Dilab\CakeMongo;


use Cake\TestSuite\TestCase;
use Cake\ORM\Marshaller;

class MarshallerTest extends TestCase
{
    /**
     * @var Marshaller
     */
    private $Marshaller;

    public function setUp()
    {
        parent::setUp();
        $this->Marshaller = makeMarshaller();
    }

    public function tearDown()
    {
        parent::tearDown();
        unset($this->Marshaller);
    }

}
