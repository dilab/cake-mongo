<?php


namespace Dilab\CakeMongo;


use Cake\Datasource\ConnectionManager;
use Cake\TestSuite\TestCase;

class MarshallerTest extends TestCase
{
    /**
     * Fixtures for this test.
     *
     * @var array
     */
    public $fixtures = ['plugin.dilab/cake_mongo.articles'];

    /**
     * @var Collection
     */
    private $collection;

    /**
     * Setup method.
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $this->collection = new Collection([
            'name' => 'articles',
            'connection' => ConnectionManager::get('test')
        ]);
    }

    /**
     * test marshalling a simple object.
     *
     * @return void
     */
    public function testOneSimple()
    {
        $this->markTestIncomplete();
        $data = [
            'title' => 'Testing',
            'body' => 'Elastic text',
            'user_id' => 1,
        ];
        $marshaller = new Marshaller($this->collection);
        $result = $marshaller->one($data);

        $this->assertInstanceOf('Dilab\CakeMongo\Document', $result);
        $this->assertSame($data['title'], $result->title);
        $this->assertSame($data['body'], $result->body);
        $this->assertSame($data['user_id'], $result->user_id);
    }

}
