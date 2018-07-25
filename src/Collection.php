<?php

namespace Imdad\CakeMongo;

use Cake\Core\App;
use Cake\Datasource\EntityInterface;
use Cake\Datasource\RepositoryInterface;
use Cake\Datasource\RulesAwareTrait;
use Cake\Event\EventDispatcherInterface;
use Cake\Event\EventDispatcherTrait;
use Cake\Event\EventListenerInterface;
use Cake\ORM\RulesChecker;
use Cake\Utility\Inflector;
use Cake\Validation\ValidatorAwareTrait;
use Imdad\CakeMongo\Datasource\MappingSchema;
use Imdad\CakeMongo\Exception\NotFoundException;
use Imdad\CakeMongo\Marshaller;
use MongoDB\BSON\ObjectID;
use MongoDB\Driver\Exception\InvalidArgumentException;

/**
 * Base class for mapping collections in a database.
 *
 * A collection in MongoDb is approximately equivalent to a table
 * in a relational datastore. While a database can
 * have multiple collections, this ODM maps
 * each collection in a database to a class.
 */
class Collection implements RepositoryInterface, EventListenerInterface, EventDispatcherInterface
{
    use EventDispatcherTrait;
    use ValidatorAwareTrait;
    use RulesAwareTrait;

    /**
     * Default validator name.
     *
     * @var string
     */
    const DEFAULT_VALIDATOR = 'default';

    /**
     * Validator provider name.
     *
     * @var string
     */
    const VALIDATOR_PROVIDER_NAME = 'collection';

    /**
     * The name of the field that represents the primary key in the collection
     *
     * @var string|array
     */
    protected $_primaryKey;

    /**
     * Connection instance
     *
     * @var \Imdad\CakeMongo\Datasource\Connection
     */
    protected $_connection;

    /**
     * The name of the MongoDB collection this class represents
     *
     * @var string
     */
    protected $_name;

    /**
     * The name of the class that represent a single document for this collection
     *
     * @var string
     */
    protected $_documentClass;

    /**
     * The mapping schema for this type.
     *
     * @var MappingSchema
     */
    protected $schema;

    /**
     * Constructor
     *
     * ### Options
     *
     * - `connection` The Connection instance.
     * - `name` The name of the collection. If this isn't set the name will be inferred from the class name.
     *
     *
     * @param array $config The configuration options, see above.
     */
    public function __construct(array $config = [])
    {
        if (!empty($config['connection'])) {
            $this->connection($config['connection']);
        }

        if (!empty($config['name'])) {
            $this->name($config['name']);
        }
    }

    /**
     * Returns the connection instance or sets a new one
     *
     * @param \Imdad\CakeMongo\Datasource\Connection $conn the new connection instance
     * @return \Imdad\CakeMongo\Datasource\Connection
     */
    public function connection($conn = null)
    {
        if ($conn === null) {
            return $this->_connection;
        }

        return $this->_connection = $conn;
    }

    /**
     * Returns the collection name or sets a new one
     *
     * @param string $name the new type name
     * @return string
     */
    public function name($name = null)
    {
        if ($name !== null) {
            $this->_name = $name;
        }

        if ($this->_name === null) {
            $name = namespaceSplit(get_class($this));
            $name = substr(end($name), 0, -4);
            if (empty($name)) {
                $name = '*';
            }
            $this->_name = Inflector::underscore($name);
        }

        return $this->_name;
    }

    /**
     * Sets the type name / alias.
     *
     * @param string $alias Table alias
     * @return $this
     */
    public function setAlias($alias)
    {
        $this->name($alias);

        return $this;
    }

    /**
     * Returns the type name / alias.
     *
     * @return string
     */
    public function getAlias()
    {
        return $this->name();
    }

    /**
     * Calls a finder method directly and applies it to the passed query
     *
     * @param string $type name of the finder to be called
     * @param \Imdad\CakeMongo\Query $query The query object to apply the finder options to
     * @param array $options List of options to pass to the finder
     * @return \Imdad\CakeMongo\Query
     * @throws \BadMethodCallException
     */
    public function callFinder($type, Query $query, array $options = [])
    {
        $query->applyOptions($options);
        $options = $query->getOptions();
        $finder = 'find' . ucfirst($type);
        if (method_exists($this, $finder)) {
            return $this->{$finder}($query, $options);
        }

        throw new \BadMethodCallException(
            sprintf('Unknown finder method "%s"', $type)
        );
    }

    /**
     * Creates a new Query for this repository and applies some defaults based on the
     * type of search that was selected.
     *
     * ### Model.beforeFind event
     *
     * Each find() will trigger a `Model.beforeFind` event for all attached
     * listeners. Any listener can set a valid result set using $query
     *
     * @param string $type the type of query to perform
     * @param array $options An array that will be passed to Query::applyOptions
     * @return \Imdad\CakeMongo\Query
     */
    public function find($type = 'all', $options = [])
    {
        $query = $this->query();

        return $this->callFinder($type, $query, $options);
    }

    /**
     * Returns the query as passed
     *
     * @param \Imdad\CakeMongo\Query $query An MongoDB query object
     * @param array $options An array of options to be used for query logic
     * @return \Imdad\CakeMongo\Query
     */
    public function findAll(Query $query, array $options = [])
    {
        return $query;
    }

    /**
     * Returns the class used to hydrate rows for this table or sets
     * a new one
     *
     * @param string $name the name of the class to use
     * @throws \RuntimeException when the entity class cannot be found
     * @return string
     */
    public function entityClass($name = null)
    {
        if ($name === null && !$this->_documentClass) {
            $default = '\Imdad\CakeMongo\Document';
            $self = get_called_class();
            $parts = explode('\\', $self);

            if ($self === __CLASS__ || count($parts) < 3) {
                return $this->_documentClass = $default;
            }

            $alias = Inflector::singularize(substr(array_pop($parts), 0, -4));
            $name = implode('\\', array_slice($parts, 0, -1)) . '\Document\\' . $alias;
            if (!class_exists($name)) {
                return $this->_documentClass = $default;
            }
        }

        if ($name !== null) {
            $class = App::classname($name, 'Model/Document');
            $this->_documentClass = $class;
        }

        if (!$this->_documentClass) {
            throw new \RuntimeException(sprintf('Missing document class "%s"', $class));
        }

        return $this->_documentClass;
    }

    /**
     * Get/set the collection/table name for this type.
     *
     * @param string $table The 'table' name for this type.
     * @return string
     */
    public function table($table = null)
    {
        return $this->name($table);
    }

    /**
     * Get the alias for this Type.
     *
     * This method is just an alias of name().
     *
     * @param string $alias The new type name
     * @return string
     */
    public function alias($alias = null)
    {
        return $this->name($alias);
    }

    /**
     * Get the mapping data from the index type.
     *
     * This will fetch the schema from ElasticSearch the first
     * time this method is called.
     *
     *
     * @return array
     */
    public function schema()
    {
        if ($this->schema !== null) {
            return $this->schema;
        }

        $name = $this->name();

//        $internalCollection = $this->connection()->getDatabase()->selectCollection($name);

        $this->schema = new MappingSchema($name, []);

        return $this->schema;
    }

    /**
     * Check whether or not a field exists in the mapping.
     *
     * @param string $field The field to check.
     * @return bool
     */
    public function hasField($field)
    {
        return true;
//        $mapping = $this->schema();

//        return $mapping->field($field) !== null;
    }

    /**
     * @{inheritdoc}
     *
     * Any key present in the options array will be translated as a GET argument
     * when getting the document by its id. This is often useful whe you need to
     * specify the parent or routing.
     *
     * This method will not trigger the Model.beforeFind callback as it does not use
     * queries for the search, but a faster key lookup to the search index.
     *
     * @param string $primaryKey The document's primary key
     * @param array $options An array of options
     * @throws \Imdad\CakeMongo\Exception\NotFoundException if no document exist with such id
     * @return \Imdad\CakeMongo\Document A new CakeMongo document entity
     */
    public function get($primaryKey, $options = [])
    {
        $internalCollection = $this->connection()->getDatabase()->selectCollection($this->name());
        $result = $internalCollection->findOne(['_id' => new ObjectID($primaryKey)], $options);
        if (empty($result)) {
            throw new NotFoundException('MongoDB Record not found');
        }

        $class = $this->entityClass();

        $options = [
            'markNew' => false,
            'markClean' => true,
            'useSetters' => false,
            'source' => $this->name(),
        ];
//        $data = $result->getData();
        //        $data['id'] = $result->getId();
        //        foreach ($this->embedded() as $embed) {
        //            $prop = $embed->property();
        //            if (isset($data[$prop])) {
        //                $data[$prop] = $embed->hydrate($data[$prop], $options);
        //            }
        //        }

        $data = (array) $result->bsonSerialize();
        $data['id'] = (string) $data['_id'];
        unset($data['_id']);

        return new $class($data, $options);
    }

    public function query()
    {
        return new Query($this);
    }

    public function updateAll($fields, $conditions)
    {
        // TODO: Implement updateAll() method.
    }

    /**
     * Delete all matching records.
     *
     * Deletes all records matching the provided conditions.
     *
     * This method will *not* trigger beforeDelete/afterDelete events. If you
     * need those first load a collection of records and delete them.
     *
     * @param array $conditions An array of conditions, similar to those used with find()
     * @return bool Success Returns true if one or more rows are effected.
     * @see RepositoryInterface::delete()
     */
    public function deleteAll($conditions)
    {
        $query = $this->query();
        $query->where($conditions);

        $internalCollection = $this->connection()->getDatabase()->selectCollection($this->name());

        $deleteResult = $internalCollection->deleteMany($query->compileQuery()['filter']);

//        $type = $this->connection()->getIndex()->getType($this->name());
        //        $response = $type->deleteByQuery($query->compileQuery());

        return $deleteResult->isAcknowledged();
    }

    /**
     * Returns true if there is any record in this repository matching the specified
     * conditions.
     *
     * @param array $conditions list of conditions to pass to the query
     * @return bool
     */
    public function exists($conditions)
    {
        try {

            $query = $this->query();

            if (count($conditions) && isset($conditions['id'])) {
                $query->where(function ($builder) use ($conditions) {
                    return $builder->eq('_id', new ObjectID($conditions['id']));
                });
            } else {
                $query->where($conditions);
            }

            $internalCollection = $this->connection()->getDatabase()->selectCollection($this->name());

            return $internalCollection->count($query->compileQuery()['filter']) > 0;

        } catch (InvalidArgumentException $e) {

            return false;

        }

    }

    /**
     * Persists an entity based on the fields that are marked as dirty and
     * returns the same entity after a successful save or false in case
     * of any error.
     *
     * Triggers the `Model.beforeSave` and `Model.afterSave` events.
     *
     * ## Options
     *
     * - `checkRules` Defaults to true. Check deletion rules before deleting the record.
     *
     * @param \Cake\Datasource\EntityInterface $entity The entity to be saved
     * @param array $options An array of options to be used for the event
     * @return \Cake\Datasource\EntityInterface|bool
     */
    public function save(EntityInterface $entity, $options = [])
    {
        $options += ['checkRules' => true];
        $options = new \ArrayObject($options);
        $event = $this->dispatchEvent('Model.beforeSave', [
            'entity' => $entity,
            'options' => $options,
        ]);
        if ($event->isStopped()) {
            return $event->result;
        }
        if ($entity->errors()) {
            return false;
        }

        $mode = $entity->isNew() ? RulesChecker::CREATE : RulesChecker::UPDATE;
        if ($options['checkRules'] && !$this->checkRules($entity, $mode, $options)) {
            return false;
        }

        $collection = $this->connection()->getDatabase()->selectCollection($this->name());
        $id = $entity->id ?: null;
        $data = $entity->toArray();
        unset($data['id'], $data['_id']);

        if (null == $id) {

            $insertOneResult = $collection->insertOne($data);

            $entity->id = (string) $insertOneResult->getInsertedId();

        } else {

            $collection->updateOne(
                ['_id' => new ObjectID($id)],
                ['$set' => $data]
            );

        }

        $entity->isNew(false);
        $entity->setSource($this->name());
        $entity->clean();

        $this->dispatchEvent('Model.afterSave', [
            'entity' => $entity,
            'options' => $options,
        ]);

        return $entity;
    }

    /**
     * Delete a single entity.
     *
     * Deletes an entity and possibly related associations from the database
     * based on the 'dependent' option used when defining the association.
     *
     * Triggers the `Model.beforeDelete` and `Model.afterDelete` events.
     *
     * @param \Cake\Datasource\EntityInterface $entity The entity to remove.
     * @param array $options The options for the delete.
     * @return bool success
     */
    public function delete(EntityInterface $entity, $options = [])
    {
        if (!$entity->has('id')) {
            $msg = 'Deleting requires an "id" value.';
            throw new \InvalidArgumentException($msg);
        }
        $options += ['checkRules' => true];
        $options = new \ArrayObject($options);
        $event = $this->dispatchEvent('Model.beforeDelete', [
            'entity' => $entity,
            'options' => $options,
        ]);
        if ($event->isStopped()) {
            return $event->result;
        }
        if (!$this->checkRules($entity, RulesChecker::DELETE, $options)) {
            return false;
        }

        $data = $entity->toArray();
        $collection = $this->connection()->getDatabase()->selectCollection($this->name());
        $deleteResult = $collection->deleteOne(['_id' => new ObjectID($data['id'])]);

        $this->dispatchEvent('Model.afterDelete', [
            'entity' => $entity,
            'options' => $options,
        ]);

        return (1 == $deleteResult->getDeletedCount());
    }

    /**
     * Create a new entity
     *
     * This is most useful when hydrating request data back into entities.
     * For example, in your controller code:
     *
     * ```
     * $article = $this->Articles->newEntity($this->request->data());
     * ```
     *
     * The hydrated entity will correctly do an insert/update based
     * on the primary key data existing in the database when the entity
     * is saved. Until the entity is saved, it will be a detached record.
     *
     * @param array|null $data The data to build an entity with.
     * @param array $options A list of options for the object hydration.
     * @return \Cake\Datasource\EntityInterface
     */
    public function newEntity($data = null, array $options = [])
    {
        if ($data === null) {
            $class = $this->entityClass();

            return new $class([], ['source' => $this->name()]);
        }

        return $this->marshaller()->one($data, $options);
    }

    /**
     * Create a list of entities + associated entities from an array.
     *
     * This is most useful when hydrating request data back into entities.
     * For example, in your controller code:
     *
     * ```
     * $articles = $this->Articles->newEntities($this->request->data());
     * ```
     *
     * The hydrated entities can then be iterated and saved.
     *
     * @param array $data The data to build an entity with.
     * @param array $options A list of options for the objects hydration.
     * @return array An array of hydrated records.
     */
    public function newEntities(array $data, array $options = [])
    {
        return $this->marshaller()->many($data, $options);
    }

    /**
     * Merges the passed `$data` into `$entity` respecting the accessible
     * fields configured on the entity. Returns the same entity after being
     * altered.
     *
     * This is most useful when editing an existing entity using request data:
     *
     * ```
     * $article = $this->Articles->patchEntity($article, $this->request->data());
     * ```
     *
     * @param \Cake\Datasource\EntityInterface $entity the entity that will get the
     * data merged in
     * @param array $data key value list of fields to be merged into the entity
     * @param array $options A list of options for the object hydration.
     * @return \Cake\Datasource\EntityInterface
     */
    public function patchEntity(EntityInterface $entity, array $data, array $options = [])
    {
        $marshaller = $this->marshaller();

        return $marshaller->merge($entity, $data, $options);
    }

    /**
     * Merges each of the elements passed in `$data` into the entities
     * found in `$entities` respecting the accessible fields configured on the entities.
     * Merging is done by matching the primary key in each of the elements in `$data`
     * and `$entities`.
     *
     * This is most useful when editing a list of existing entities using request data:
     *
     * ```
     * $article = $this->Articles->patchEntities($articles, $this->request->data());
     * ```
     *
     * @param array|\Traversable $entities the entities that will get the
     * data merged in
     * @param array $data list of arrays to be merged into the entities
     * @param array $options A list of options for the objects hydration.
     * @return array
     */
    public function patchEntities($entities, array $data, array $options = [])
    {
        $marshaller = $this->marshaller();

        return $marshaller->mergeMany($entities, $data, $options);
    }

    /**
     * Get the callbacks this Collection is interested in.
     *
     * By implementing the conventional methods a Type class is assumed
     * to be interested in the related event.
     *
     * Override this method if you need to add non-conventional event listeners.
     * Or if you want you table to listen to non-standard events.
     *
     * The conventional method map is:
     *
     * - Model.beforeMarshal => beforeMarshal
     * - Model.beforeFind => beforeFind
     * - Model.beforeSave => beforeSave
     * - Model.afterSave => afterSave
     * - Model.beforeDelete => beforeDelete
     * - Model.afterDelete => afterDelete
     * - Model.beforeRules => beforeRules
     * - Model.afterRules => afterRules
     *
     * @return array
     */
    public function implementedEvents()
    {
        $eventMap = [
            'Model.beforeMarshal' => 'beforeMarshal',
            'Model.beforeFind' => 'beforeFind',
            'Model.beforeSave' => 'beforeSave',
            'Model.afterSave' => 'afterSave',
            'Model.beforeDelete' => 'beforeDelete',
            'Model.afterDelete' => 'afterDelete',
            'Model.beforeRules' => 'beforeRules',
            'Model.afterRules' => 'afterRules',
        ];
        $events = [];

        foreach ($eventMap as $event => $method) {
            if (!method_exists($this, $method)) {
                continue;
            }
            $events[$event] = $method;
        }

        return $events;
    }

    /**
     * The default connection name to inject when creating an instance.
     *
     * @return string
     */
    public static function defaultConnectionName()
    {
        return 'cake_mongo';
    }

    /**
     * Get a marshaller for this Collection instance.
     *
     * @return \Imdad\CakeMongo\Marshaller
     */
    public function marshaller()
    {
        return new Marshaller($this);
    }

}
