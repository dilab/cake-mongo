<?php
namespace Imdad\CakeMongo\Test\TestCase;

use Cake\Core\Configure;
use Cake\Core\Plugin;
use Cake\TestSuite\TestCase;
use Imdad\CakeMongo\Collection;
use Imdad\CakeMongo\CollectionRegistry;

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
        $this->assertInstanceOf('Imdad\CakeMongo\Collection', $result);
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
        $this->assertInstanceOf('Imdad\CakeMongo\Collection', $result);
        $this->assertEquals('droids', $result->name());

        $result = CollectionRegistry::get('R2D2', ['className' => 'Droids']);
        $this->assertInstanceOf('Imdad\CakeMongo\Collection', $result);
        $this->assertEquals('r2_d2', $result->name(), 'The name should be derived from the alias');

        $result = CollectionRegistry::get('C3P0', ['className' => 'Droids', 'name' => 'droids']);
        $this->assertInstanceOf('Imdad\CakeMongo\Collection', $result);
        $this->assertEquals('droids', $result->name(), 'The name should be taken from options');

        $result = CollectionRegistry::get('Funky.Chipmunks');
        $this->assertInstanceOf('Imdad\CakeMongo\Collection', $result);
        $this->assertEquals('chipmunks', $result->name(), 'The name should be derived from the alias');

        $result = CollectionRegistry::get('Awesome', ['className' => 'Funky.Monkies']);
        $this->assertInstanceOf('Imdad\CakeMongo\Collection', $result);
        $this->assertEquals('awesome', $result->name(), 'The name should be derived from the alias');

        $result = CollectionRegistry::get('Stuff', ['className' => 'Imdad\CakeMongo\Collection']);
        $this->assertInstanceOf('Imdad\CakeMongo\Collection', $result);
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

    /**
     * Test get() with same-alias models in different plugins
     *
     * There should be no internal cache-confusion
     *
     * @return void
     */
    public function testGetMultiplePlugins()
    {
        Plugin::load('TestPlugin');
        Plugin::load('TestPluginTwo');

        $app = CollectionRegistry::get('Comments');
        $plugin1 = CollectionRegistry::get('TestPlugin.Comments');
        $plugin2 = CollectionRegistry::get('TestPluginTwo.Comments');

        $this->assertInstanceOf('Imdad\CakeMongo\Collection', $app, 'Should be a generic instance');
        $this->assertInstanceOf('TestPlugin\Model\Collection\CommentsCollection', $plugin1, 'Should be a concrete class');
        $this->assertInstanceOf('Imdad\CakeMongo\Collection', $plugin2, 'Should be a plugin 2 generic instance');

        $plugin2 = CollectionRegistry::get('TestPluginTwo.Comments');
        $plugin1 = CollectionRegistry::get('TestPlugin.Comments');
        $app = CollectionRegistry::get('Comments');

        $this->assertInstanceOf('Imdad\CakeMongo\Collection', $app, 'Should still be a generic instance');
        $this->assertInstanceOf('TestPlugin\Model\Collection\CommentsCollection', $plugin1, 'Should still be a concrete class');
        $this->assertInstanceOf('Imdad\CakeMongo\Collection', $plugin2, 'Should still be a plugin 2 generic instance');
    }

    /**
     * Test get() with plugin aliases + className option.
     *
     * @return void
     */
    public function testGetPluginWithClassNameOption()
    {
        Plugin::load('TestPlugin');
        $table = CollectionRegistry::get('MyComments', [
            'className' => 'TestPlugin.Comments',
        ]);
        $class = 'TestPlugin\Model\Collection\CommentsCollection';
        $this->assertInstanceOf($class, $table);
        $this->assertFalse(CollectionRegistry::exists('Comments'), 'Class name should not exist');
        $this->assertFalse(CollectionRegistry::exists('TestPlugin.Comments'), 'Full class alias should not exist');
        $this->assertTrue(CollectionRegistry::exists('MyComments'), 'Class name should exist');

        $second = CollectionRegistry::get('MyComments');
        $this->assertSame($table, $second);
    }

    /**
     * Test get() with full namespaced classname
     *
     * @return void
     */
    public function testGetPluginWithFullNamespaceName()
    {
        Plugin::load('TestPlugin');
        $class = 'TestPlugin\Model\Collection\CommentsCollection';
        $table = CollectionRegistry::get('Comments', [
            'className' => $class,
        ]);
        $this->assertInstanceOf($class, $table);
        $this->assertFalse(CollectionRegistry::exists('TestPlugin.Comments'), 'Full class alias should not exist');
        $this->assertTrue(CollectionRegistry::exists('Comments'), 'Class name should exist');
    }

    /**
     * Test setting an instance.
     *
     * @return void
     */
    public function testSet()
    {
        $mock = $this->getMock('Imdad\CakeMongo\Collection');
        $this->assertSame($mock, CollectionRegistry::set('Articles', $mock));
        $this->assertSame($mock, CollectionRegistry::get('Articles'));
    }

    /**
     * Test setting an instance with plugin syntax aliases
     *
     * @return void
     */
    public function testSetPlugin()
    {
        Plugin::load('TestPlugin');

        $mock = $this->getMock('TestPlugin\Model\Collection\CommentsCollection');

        $this->assertSame($mock, CollectionRegistry::set('TestPlugin.Comments', $mock));
        $this->assertSame($mock, CollectionRegistry::get('TestPlugin.Comments'));
    }

    /**
     * Tests remove an instance
     *
     * @return void
     */
    public function testRemove()
    {
        $first = CollectionRegistry::get('Comments');

        $this->assertTrue(CollectionRegistry::exists('Comments'));

        CollectionRegistry::remove('Comments');
        $this->assertFalse(CollectionRegistry::exists('Comments'));

        $second = CollectionRegistry::get('Comments');

        $this->assertNotSame($first, $second, 'Should be different objects, as the reference to the first was destroyed');
        $this->assertTrue(CollectionRegistry::exists('Comments'));
    }

    /**
     * testRemovePlugin
     *
     * Removing a plugin-prefixed model should not affect any other
     * plugin-prefixed model, or app model.
     * Removing an app model should not affect any other
     * plugin-prefixed model.
     *
     * @return void
     */
    public function testRemovePlugin()
    {
        Plugin::load('TestPlugin');
        Plugin::load('TestPluginTwo');

        $app = CollectionRegistry::get('Comments');
        CollectionRegistry::get('TestPlugin.Comments');
        $plugin = CollectionRegistry::get('TestPluginTwo.Comments');

        $this->assertTrue(CollectionRegistry::exists('Comments'));
        $this->assertTrue(CollectionRegistry::exists('TestPlugin.Comments'));
        $this->assertTrue(CollectionRegistry::exists('TestPluginTwo.Comments'));

        CollectionRegistry::remove('TestPlugin.Comments');

        $this->assertTrue(CollectionRegistry::exists('Comments'));
        $this->assertFalse(CollectionRegistry::exists('TestPlugin.Comments'));
        $this->assertTrue(CollectionRegistry::exists('TestPluginTwo.Comments'));

        $app2 = CollectionRegistry::get('Comments');
        $plugin2 = CollectionRegistry::get('TestPluginTwo.Comments');

        $this->assertSame($app, $app2, 'Should be the same Comments object');
        $this->assertSame($plugin, $plugin2, 'Should be the same TestPluginTwo.Comments object');

        CollectionRegistry::remove('Comments');

        $this->assertFalse(CollectionRegistry::exists('Comments'));
        $this->assertFalse(CollectionRegistry::exists('TestPlugin.Comments'));
        $this->assertTrue(CollectionRegistry::exists('TestPluginTwo.Comments'));

        $plugin3 = CollectionRegistry::get('TestPluginTwo.Comments');

        $this->assertSame($plugin, $plugin3, 'Should be the same TestPluginTwo.Comments object');
    }

}
