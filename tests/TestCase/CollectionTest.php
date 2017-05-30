<?php

namespace Dilab\CakeMongo\Test\TestCase;

use Cake\Datasource\ConnectionManager;
use Cake\TestSuite\TestCase;
use Dilab\CakeMongo\Collection;
use Dilab\CakeMongo\Document;

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
            'id' => '507f191e810c19729de860ea'
        ], $result->toArray());
        $this->assertFalse($result->dirty());
        $this->assertFalse($result->isNew());
    }

    /**
     * Test that newEntity is wired up.
     *
     * @return void
     */
    public function testNewEntity()
    {
        $data = [
            'title' => 'A newer title'
        ];
        $result = $this->collection->newEntity($data);
        $this->assertInstanceOf('Dilab\CakeMongo\Document', $result);
        $this->assertSame($data, $result->toArray());
        $this->assertEquals('articles', $result->source());
    }

    /**
     * Test that newEntities is wired up.
     *
     * @return void
     */
    public function testNewEntities()
    {
        $data = [
            [
                'title' => 'A newer title'
            ],
            [
                'title' => 'A second title'
            ],
        ];
        $result = $this->collection->newEntities($data);
        $this->assertCount(2, $result);
        $this->assertInstanceOf('Dilab\CakeMongo\Document', $result[0]);
        $this->assertInstanceOf('Dilab\CakeMongo\Document', $result[1]);
        $this->assertSame($data[0], $result[0]->toArray());
        $this->assertSame($data[1], $result[1]->toArray());
    }

    /**
     * Test saving a new document.
     *
     * @return void
     */
    public function testSaveNew()
    {
        $doc = new Document([
            'title' => 'A brand new article',
            'body' => 'Some new content'
        ], ['markNew' => true]);
        $this->assertSame($doc, $this->collection->save($doc));
        $this->assertNotEmpty($doc->id, 'Should get an id');
        $this->assertFalse($doc->isNew(), 'Not new anymore.');
        $this->assertFalse($doc->isDirty(), 'Not dirty anymore.');

        $result = $this->collection->get($doc->id);
        $this->assertEquals($doc->title, $result->title);
        $this->assertEquals($doc->body, $result->body);
        $this->assertEquals('articles', $result->getSource());
    }

    /**
     * Test saving a new document.
     *
     * @return void
     */
    public function testSaveUpdate()
    {
        $doc = new Document([
            'id' => '507f191e810c19729de860ea',
            'title' => 'A brand new article',
            'body' => 'Some new content'
        ], ['markNew' => false]);
        $this->assertSame($doc, $this->collection->save($doc));
        $this->assertFalse($doc->isNew(), 'Not new.');
        $this->assertFalse($doc->isDirty(), 'Not dirty anymore.');
        $this->assertEquals('articles', $doc->getSource());
    }

    /**
     * Test saving a new document that contains errors
     *
     * @return void
     */
    public function testSaveDoesNotSaveDocumentWithErrors()
    {
        $doc = new Document([
            'id' => '507f191e810c19729de860ea',
            'title' => 'A brand new article',
            'body' => 'Some new content'
        ], ['markNew' => false]);
        $doc->setErrors(['title' => ['bad news']]);
        $this->assertFalse($this->collection->save($doc), 'Should not save.');
    }

    /**
     * Test save triggers events.
     *
     * @return void
     */
    public function testSaveEvents()
    {
        $doc = $this->collection->get('507f191e810c19729de860ea');
        $doc->title = 'A new title';

        $called = 0;
        $this->collection->eventManager()->on(
            'Model.beforeSave',
            function ($event, $entity, $options) use ($doc, &$called) {
                $called++;
                $this->assertSame($doc, $entity);
                $this->assertInstanceOf('ArrayObject', $options);
            }
        );
        $this->collection->eventManager()->on(
            'Model.afterSave',
            function ($event, $entity, $options) use ($doc, &$called) {
                $called++;
                $this->assertInstanceOf('ArrayObject', $options);
                $this->assertSame($doc, $entity);
                $this->assertFalse($doc->isNew(), 'Should not be new');
                $this->assertFalse($doc->isDirty(), 'Should not be dirty');
            }
        );
        $this->collection->save($doc);
        $this->assertEquals(2, $called);
    }

    /**
     * Test beforeSave abort.
     *
     * @return void
     */
    public function testSaveBeforeSaveAbort()
    {
        $doc = $this->collection->get('507f191e810c19729de860ea');
        $doc->title = 'new title';
        $this->collection->eventManager()->on('Model.beforeSave', function ($event, $entity, $options) use ($doc) {
            $event->stopPropagation();

            return 'kaboom';
        });
        $this->collection->eventManager()->on('Model.afterSave', function () {
            $this->fail('Should not be fired');
        });
        $this->assertSame('kaboom', $this->collection->save($doc));
    }

    /**
     * Test save with embedded documents.
     *
     * @return void
     */
    public function testSaveEmbedOne()
    {
        $this->markTestSkipped('Implement Embed Later');
        $entity = new Document([
            'title' => 'A brand new article',
            'body' => 'Some new content',
            'user' => new Document(['username' => 'sarah'])
        ]);
        $this->type->embedOne('User');
        $this->type->save($entity);

        $compare = $this->type->get($entity->id);
        $this->assertInstanceOf('Cake\ElasticSearch\Document', $compare->user);
        $this->assertEquals('sarah', $compare->user->username);
    }

    /**
     * Test save with embedded documents.
     *
     * @return void
     */
    public function testSaveEmbedMany()
    {
        $this->markTestSkipped('Implement Embed Later');

        $entity = new Document([
            'title' => 'A brand new article',
            'body' => 'Some new content',
            'comments' => [
                new Document(['comment' => 'Nice post']),
                new Document(['comment' => 'Awesome!']),
            ]
        ]);
        $this->type->embedMany('Comments');
        $this->type->save($entity);

        $compare = $this->type->get($entity->id);
        $this->assertInstanceOf('Cake\ElasticSearch\Document', $compare->comments[0]);
        $this->assertInstanceOf('Cake\ElasticSearch\Document', $compare->comments[1]);
        $this->assertEquals('Nice post', $compare->comments[0]->comment);
        $this->assertEquals('Awesome!', $compare->comments[1]->comment);
    }

    /**
     * Test that rules can prevent save.
     *
     * @return void
     */
    public function testSaveWithRulesCreate()
    {
        $this->collection->eventManager()->on('Model.buildRules', function ($event, $rules) {
            $rules->addCreate(function ($doc) {
                return 'Did not work';
            }, ['errorField' => 'name']);
        });

        $doc = new Document(['title' => 'rules are checked']);
        $this->assertFalse($this->collection->save($doc), 'Save should fail');

        $doc->clean();
        $doc->id = '507f191e810c19729de860ea';
        $doc->isNew(false);
        $this->assertSame($doc, $this->collection->save($doc), 'Save should pass, not new anymore.');
    }

    /**
     * Test that rules can prevent save.
     *
     * @return void
     */
    public function testSaveWithRulesUpdate()
    {
        $this->collection->eventManager()->on('Model.buildRules', function ($event, $rules) {
            $rules->addUpdate(function ($doc) {
                return 'Did not work';
            }, ['errorField' => 'name']);
        });

        $doc = new Document(['title' => 'update rules'], ['markNew' => false]);
        $this->assertFalse($this->collection->save($doc), 'Save should fail');
    }

    /**
     * Test to make sure double save works correctly
     *
     * @return void
     */
    public function testDoubleSave()
    {
        $doc = new Document([
            'title' => 'A brand new article',
            'body' => 'Some new content'
        ], ['markNew' => true]);
        $this->assertSame($doc, $this->collection->save($doc));
        $this->assertNotEmpty($doc->id, 'Should get an id');

        $this->assertSame($doc, $this->collection->save($doc));
        $this->assertNotEmpty($doc->id, 'Should get an id');
    }

    /**
     * Test deleting a document.
     *
     * @return void
     */
    public function testDeleteBasic()
    {
        $doc = $this->collection->get('507f191e810c19729de860ea');
        $this->assertTrue($this->collection->delete($doc));

        $dead = $this->collection->find()->where(['id' => '507f191e810c19729de860ea'])->first();
        $this->assertNull($dead, 'No record.');
    }

    /**
     * Test deletion prevented by rules
     *
     * @return void
     */
    public function testDeleteRules()
    {
        $this->collection->rulesChecker()->addDelete(function () {
            return 'not good';
        }, ['errorField' => 'title']);
        $doc = $this->collection->get('507f191e810c19729de860ea');

        $this->assertFalse($this->collection->delete($doc));
        $this->assertNotEmpty($doc->getError('title'));
    }

    /**
     * Test delete triggers events.
     *
     * @return void
     */
    public function testDeleteEvents()
    {
        $called = 0;
        $doc = $this->collection->get('507f191e810c19729de860ea');
        $this->collection->eventManager()->on(
            'Model.beforeDelete',
            function ($event, $entity, $options) use ($doc, &$called) {
                $called++;
                $this->assertSame($doc, $entity);
                $this->assertInstanceOf('ArrayObject', $options);
            }
        );
        $this->collection->eventManager()->on(
            'Model.afterDelete',
            function ($event, $entity, $options) use ($doc, &$called) {
                $called++;
                $this->assertSame($doc, $entity);
                $this->assertInstanceOf('ArrayObject', $options);
            }
        );
        $this->assertTrue($this->collection->delete($doc));
        $this->assertEquals(2, $called);
    }

    /**
     * Test beforeDelete abort.
     *
     * @return void
     */
    public function testDeleteBeforeDeleteAbort()
    {
        $doc = $this->collection->get('507f191e810c19729de860ea');
        $this->collection->eventManager()->on('Model.beforeDelete', function ($event, $entity, $options) use ($doc) {
            $event->stopPropagation();

            return 'kaboom';
        });
        $this->collection->eventManager()->on('Model.afterDelete', function () {
            $this->fail('Should not be fired');
        });
        $this->assertSame('kaboom', $this->collection->delete($doc));
    }

    /**
     * Test deleting a new document
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Deleting requires an "id" value.
     * @return void
     */
    public function testDeleteMissing()
    {
        $doc = new Document(['title' => 'not there.']);
        $this->collection->delete($doc);
    }

    /**
     * Test getting and setting validators.
     *
     * @return void
     */
    public function testValidatorSetAndGet()
    {
        $result = $this->collection->validator();

        $this->assertInstanceOf('Cake\Validation\Validator', $result);
        $this->assertSame($result, $this->collection->validator(), 'validator instances are persistent');
        $this->assertSame($this->collection, $result->provider('collection'), 'type bound as provider');
    }

    /**
     * Test buildValidator event
     *
     * @return void
     */
    public function testValidatorTriggerEvent()
    {
        $called = 0;
        $this->collection->eventManager()->on('Model.buildValidator', function ($event, $validator, $name) use (&$called) {
            $called++;
            $this->assertInstanceOf('Cake\Validation\Validator', $validator);
            $this->assertEquals('default', $name);
        });
        $this->collection->validator();
        $this->assertEquals(1, $called, 'Event not triggered');
    }

    /**
     * Test that exists works.
     *
     * @return void
     */
    public function testExists()
    {
        $this->assertFalse($this->collection->exists(['id' => '999999']));
        $this->assertTrue($this->collection->exists(['id' => '507f191e810c19729de860ea']));
    }

    /**
     * Test that deleteAll works.
     *
     * @return void
     */
    public function testDeleteAll()
    {
        $result = $this->collection->deleteAll(
            ['user_id in' => [1, 2]]
        );
        $this->assertTrue($result);
        $this->assertEquals(0, $this->collection->find()->count());
    }

    /**
     * Test that deleteAll works.
     *
     * @return void
     */
    public function testDeleteAllOnlySome()
    {
        $result = $this->collection->deleteAll(['body' => 'A big box of bolts and nuts.']);
        $this->assertTrue($result);
        $this->assertEquals(1, $this->collection->find()->count());
    }

    /**
     * Test the rules builder types
     *
     * @return void
     */
    public function testAddRules()
    {
        $this->collection->eventManager()->on('Model.buildRules', function ($event, $rules) {
            $rules->add(function ($doc) {
                return false;
            });
        });
        $rules = $this->collection->rulesChecker();
        $this->assertInstanceOf('Cake\Datasource\RulesChecker', $rules);

        $doc = new Document();
        $result = $rules->checkCreate($doc);
        $this->assertFalse($result, 'Rules should fail.');
    }

    /**
     * Test the alias method.
     *
     * @return void
     */
    public function testAlias()
    {
        $this->assertEquals($this->collection->name(), $this->collection->alias());
        $this->assertEquals('articles', $this->collection->alias());
    }

    /**
     * Test hasField()
     *
     * @return void
     */
    public function testHasField()
    {
        $this->assertTrue($this->collection->hasField('title'));
        $this->assertTrue($this->collection->hasField('nope'));
    }

    /**
     * Test that Collection implements the EventListenerInterface and some events.
     *
     * @return void
     */
    public function testImplementedEvents()
    {
        $this->assertInstanceOf('Cake\Event\EventListenerInterface', $this->collection);

        $collection = $this->getMock(
            'Dilab\CakeMongo\Collection',
            ['beforeFind', 'beforeSave', 'afterSave', 'beforeDelete', 'afterDelete']
        );
        $result = $collection->implementedEvents();

        $expected = [
            'Model.beforeFind' => 'beforeFind',
            'Model.beforeSave' => 'beforeSave',
            'Model.afterSave' => 'afterSave',
            'Model.beforeDelete' => 'beforeDelete',
            'Model.afterDelete' => 'afterDelete',
        ];
        $this->assertEquals($expected, $result, 'Events do not match.');
    }

    /**
     * Test that Collection patchEntity Method
     *
     * @return void
     */
    public function testPatchEntity()
    {
        $result = $this->collection->get('507f191e810c19729de860ea');
        $data = [
            'title' => 'A newer title'
        ];
        $result = $this->collection->patchEntity($result, $data);
        $this->assertInstanceOf('Dilab\CakeMongo\Document', $result);
        $this->assertSame('A newer title', $result->toArray()['title']);
    }

    /**
     * Test that Collection patchEntities Method
     *
     * @return void
     */
    public function testPatchEntities()
    {
        $result = [$this->collection->get('507f191e810c19729de860ea')];
        $data = [['title' => 'A newer title']];
        $result = $this->collection->patchEntities($result, $data);
        $this->assertInstanceOf('Dilab\CakeMongo\Document', $result[0]);
        $this->assertSame('A newer title', $result[0]->toArray()['title']);
    }
}
