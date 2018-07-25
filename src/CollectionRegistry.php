<?php

namespace Imdad\CakeMongo;

use Cake\Core\App;
use Cake\Datasource\ConnectionManager;
use Cake\Utility\Inflector;

/**
 * Factory/Registry class for Collection objects.
 *
 * Handles ensuring only one instance of each type is
 * created and that the correct connection is injected in.
 *
 * Provides an interface similar to Cake\ORM\TableRegistry.
 */
class CollectionRegistry
{
    /**
     * The map of instances in the registry.
     *
     * @var array
     */
    protected static $instances = [];

    /**
     * List of options by alias passed to get.
     *
     * @var array
     */
    protected static $options = [];

    /**
     * Clears the registry of configuration and instances.
     *
     * @return void
     */
    public static function clear()
    {
        static::$instances = [];
    }

    /**
     * Check to see if an instance exists in the registry.
     *
     * @param string $alias The alias to check for.
     * @return bool
     */
    public static function exists($alias)
    {
        return isset(static::$instances[$alias]);
    }

    /**
     * Get/Create an instance from the registry.
     *
     * When getting an instance, if it does not already exist,
     * a new instance will be created using the provide alias, and options.
     *
     * @param string $alias The name of the alias to get.
     * @param array $options Configuration options for the type constructor.
     * @return \Dilab\CakeMongo\Collection
     */
    public static function get($alias, array $options = [])
    {

        if (isset(static::$instances[$alias])) {
            if (!empty($options) && static::$options[$alias] !== $options) {
                throw new \RuntimeException(sprintf(
                    'You cannot configure "%s", it already exists in the registry.',
                    $alias
                ));
            }

            return static::$instances[$alias];
        }

        static::$options[$alias] = $options;
        list(, $classAlias) = pluginSplit($alias);
        $options = $options + ['name' => Inflector::underscore($classAlias)];

        if (empty($options['className'])) {
            $options['className'] = Inflector::camelize($alias);
        }
        $className = App::className($options['className'], 'Model/Collection', 'Collection');

        if ($className) {
            $options['className'] = $className;
        } else {
            if (!isset($options['name']) && strpos($options['className'], '\\') === false) {
                list(, $name) = pluginSplit($options['className']);
                $options['name'] = Inflector::underscore($name);
            }

            $options['className'] = 'Dilab\CakeMongo\Collection';
        }

        if (empty($options['connection'])) {
            $connectionName = $options['className']::defaultConnectionName();
            $options['connection'] = ConnectionManager::get($connectionName);
        }

        static::$instances[$alias] = new $options['className']($options);
        return static::$instances[$alias];
    }

    /**
     * Set an instance.
     *
     * @param string $alias The alias to set.
     * @param \Dilab\CakeMongo\Collection $object The type to set.
     * @return \Dilab\CakeMongo\Collection
     */
    public static function set($alias, Collection $object)
    {
        return static::$instances[$alias] = $object;
    }

    /**
     * Removes an instance from the registry.
     *
     * @param string $alias The alias to remove.
     * @return void
     */
    public static function remove($alias)
    {
        unset(static::$instances[$alias]);
    }

}
