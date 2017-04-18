<?php


namespace Dilab\CakeMongo;


use Cake\Datasource\ConnectionManager;
use Cake\TestSuite\TestCase;

/**
 * Test entity for mass assignment.
 */
class ProtectedArticle extends Document
{

    protected $_accessible = [
        'title' => true,
    ];
}

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

    /**
     * Test validation errors being set.
     *
     * @return void
     */
    public function testOneValidationErrorsSet()
    {
        $data = [
            'title' => 'Testing',
            'body' => 'Elastic text',
            'user_id' => 1,
        ];
        $this->collection->validator()
            ->add('title', 'numbery', ['rule' => 'numeric']);

        $marshaller = new Marshaller($this->collection);
        $result = $marshaller->one($data);

        $this->assertInstanceOf('Dilab\CakeMongo\Document', $result);
        $this->assertNull($result->title, 'Invalid fields are not set.');
        $this->assertSame($data['body'], $result->body);
        $this->assertSame($data['user_id'], $result->user_id);
        $this->assertNotEmpty($result->errors('title'), 'Should have an error.');
    }

    /**
     * test marshalling with fieldList
     *
     * @return void
     */
    public function testOneFieldList()
    {
        $data = [
            'title' => 'Testing',
            'body' => 'Elastic text',
            'user_id' => 1,
        ];
        $marshaller = new Marshaller($this->collection);
        $result = $marshaller->one($data, ['fieldList' => ['title']]);

        $this->assertSame($data['title'], $result->title);
        $this->assertNull($result->body);
        $this->assertNull($result->user_id);
    }

    /**
     * test marshalling with accessibleFields
     *
     * @return void
     */
    public function testOneAccsesibleFields()
    {
        $data = [
            'title' => 'Testing',
            'body' => 'Elastic text',
            'user_id' => 1,
        ];
        $this->collection->entityClass(__NAMESPACE__ . '\ProtectedArticle');

        $marshaller = new Marshaller($this->collection);
        $result = $marshaller->one($data);

        $this->assertSame($data['title'], $result->title);
        $this->assertNull($result->body);
        $this->assertNull($result->user_id);

        $result = $marshaller->one($data, ['accessibleFields' => ['body' => true]]);

        $this->assertSame($data['title'], $result->title);
        $this->assertSame($data['body'], $result->body);
        $this->assertNull($result->user_id);
    }

    /**
     * test beforeMarshal event
     *
     * @return void
     */
    public function testOneBeforeMarshalEvent()
    {
        $data = [
            'title' => 'Testing',
            'body' => 'Elastic text',
            'user_id' => 1,
        ];
        $called = 0;
        $this->collection->eventManager()->on(
            'Model.beforeMarshal',
            function ($event, $data, $options) use (&$called) {
                $called++;
                $this->assertInstanceOf('ArrayObject', $data);
                $this->assertInstanceOf('ArrayObject', $options);
            }
        );
        $marshaller = new Marshaller($this->collection);
        $marshaller->one($data);

        $this->assertEquals(1, $called, 'method should be called');
    }
}
