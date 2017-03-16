<?php


namespace Dilab\CakeMongo;


use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\TestSuite\TestCase;

/**
 * Used to test correct class is instantiated when using CollectionRegistry::get();
 */
class MyUsersCollection extends Collection
{

    /**
     * Overrides default table name
     *
     * @var string
     */
    protected $_name = 'users';
}


class CollectionRegistryTest extends TestCase
{
    /**
     * setup
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        Configure::write('App.namespace', 'TestApp');
    }

    /**
     * tear down
     *
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();
        CollectionRegistry::clear();
    }

    /**
     * Test the exists() method.
     *
     * @return void
     */
    public function testExists()
    {
        $this->assertFalse(CollectionRegistry::exists('Articles'));

        CollectionRegistry::get('Articles', ['name' => 'articles']);
        $this->assertTrue(CollectionRegistry::exists('Articles'));
    }

    /**
     * Test the exists() method with plugin-prefixed models.
     *
     * @return void
     */
    public function testExistsPlugin()
    {
        $this->assertFalse(CollectionRegistry::exists('Comments'));
        $this->assertFalse(CollectionRegistry::exists('TestPlugin.Comments'));

        CollectionRegistry::get('TestPlugin.Comments', ['type' => 'comments']);
        $this->assertFalse(CollectionRegistry::exists('Comments'), 'The Comments key should not be populated');
        $this->assertTrue(CollectionRegistry::exists('TestPlugin.Comments'), 'The plugin.alias key should now be populated');
    }

    /**
     * Test getting instances from the registry.
     *
     * @return void
     */
    public function testGet()
    {
        $result = CollectionRegistry::get('Articles', [
            'name' => 'my_articles',
        ]);
        $this->assertInstanceOf('Dilab\CakeMongo\Collection', $result);
        $this->assertEquals('my_articles', $result->name());

        $result2 = CollectionRegistry::get('Articles');
        $this->assertSame($result, $result2);
        $this->assertEquals('my_articles', $result->name());
    }

    /**
     * Are auto-models instanciated correctly? How about when they have an alias?
     *
     * @return void
     */
    public function testGetFallbacks()
    {
        $result = CollectionRegistry::get('Droids');
        $this->assertInstanceOf('Dilab\CakeMongo\Collection', $result);
        $this->assertEquals('droids', $result->name());

        $result = CollectionRegistry::get('R2D2', ['className' => 'Droids']);
        $this->assertInstanceOf('Dilab\CakeMongo\Collection', $result);
        $this->assertEquals('r2_d2', $result->name(), 'The name should be derived from the alias');

        $result = CollectionRegistry::get('C3P0', ['className' => 'Droids', 'name' => 'droids']);
        $this->assertInstanceOf('Dilab\CakeMongo\Collection', $result);
        $this->assertEquals('droids', $result->name(), 'The name should be taken from options');

        $result = CollectionRegistry::get('Funky.Chipmunks');
        $this->assertInstanceOf('Dilab\CakeMongo\Collection', $result);
        $this->assertEquals('chipmunks', $result->name(), 'The name should be derived from the alias');

        $result = CollectionRegistry::get('Awesome', ['className' => 'Funky.Monkies']);
        $this->assertInstanceOf('Dilab\CakeMongo\Collection', $result);
        $this->assertEquals('awesome', $result->name(), 'The name should be derived from the alias');

        $result = CollectionRegistry::get('Stuff', ['className' => 'Dilab\CakeMongo\Collection']);
        $this->assertInstanceOf('Dilab\CakeMongo\Collection', $result);
        $this->assertEquals('stuff', $result->name(), 'The name should be derived from the alias');
    }

    /**
     * Test get with config throws an exception if the alias exists already.
     *
     * @expectedException \RuntimeException
     * @expectedExceptionMessage You cannot configure "Users", it already exists in the registry.
     * @return void
     */
    public function testGetExistingWithConfigData()
    {
        $users = CollectionRegistry::get('Users');
        CollectionRegistry::get('Users', ['name' => 'my_users']);
    }

    /**
     * Test get() can be called several times with the same option without
     * throwing an exception.
     *
     * @return void
     */
    public function testGetWithSameOption()
    {
        $result = CollectionRegistry::get('Users', ['className' => __NAMESPACE__ . '\MyUsersCollection']);
        $result2 = CollectionRegistry::get('Users', ['className' => __NAMESPACE__ . '\MyUsersCollection']);
        $this->assertEquals($result, $result2);
    }

    /**
     * Test get() with plugin syntax aliases
     *
     * @return void
     */
    public function testGetPlugin()
    {
        $this->markTestIncomplete();
        Plugin::load('TestPlugin');
        $table = CollectionRegistry::get('TestPlugin.Comments');
        $this->assertInstanceOf('TestPlugin\Model\Collection\CommentsCollection', $table);
        $this->assertFalse(
            CollectionRegistry::exists('Comments'),
            'Short form should NOT exist'
        );
        $this->assertTrue(
            CollectionRegistry::exists('TestPlugin.Comments'),
            'Long form should exist'
        );

        $second = CollectionRegistry::get('TestPlugin.Comments');
        $this->assertSame($table, $second, 'Can fetch long form');
    }

}
