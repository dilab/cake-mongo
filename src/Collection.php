<?php


namespace Dilab\CakeMongo;

use Cake\Core\App;
use Cake\Datasource\EntityInterface;
use Cake\Datasource\RepositoryInterface;
use Cake\Event\EventDispatcherTrait;
use Cake\Utility\Inflector;
use Cake\Validation\ValidatorAwareTrait;
use MongoDB\BSON\ObjectID;

/**
 * Base class for mapping collections in a database.
 *
 * A collection in MongoDb is approximately equivalent to a table
 * in a relational datastore. While a database can
 * have multiple collections, this ODM maps
 * each collection in a database to a class.
 */
class Collection implements RepositoryInterface
{
    use EventDispatcherTrait;
    use ValidatorAwareTrait;

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
     * Connection instance
     *
     * @var \Dilab\CakeMongo\Datasource\Connection
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
     * @param \Dilab\CakeMongo\Datasource\Connection $conn the new connection instance
     * @return \Dilab\CakeMongo\Datasource\Connection
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
     * Calls a finder method directly and applies it to the passed query
     *
     * @param string $type name of the finder to be called
     * @param \Dilab\CakeMongo\Query $query The query object to apply the finder options to
     * @param array $options List of options to pass to the finder
     * @return \Dilab\CakeMongo\Query
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
     * @return \Dilab\CakeMongo\Query
     */
    public function find($type = 'all', $options = [])
    {
        $query = $this->query();

        return $this->callFinder($type, $query, $options);
    }

    /**
     * Returns the query as passed
     *
     * @param \Dilab\CakeMongo\Query $query An MongoDB query object
     * @param array $options An array of options to be used for query logic
     * @return \Dilab\CakeMongo\Query
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
            $default = '\Dilab\CakeMongo\Document';
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

    public function alias($alias = null)
    {
        // TODO: Implement alias() method.
    }


    public function hasField($field)
    {
        // TODO: Implement hasField() method.
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
     * @throws \Elastica\Exception\NotFoundException if no document exist with such id
     * @return \Cake\ElasticSearch\Document A new Elasticsearch document entity
     */
    public function get($primaryKey, $options = [])
    {
        $internalCollection = $this->connection()->getDatabase()->selectCollection($this->name());
        $result = $internalCollection->findOne(['_id' => new ObjectID($primaryKey)], $options);
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

        $data = (array)$result->bsonSerialize();
        $data['_id'] = (string)$data['_id'];

//        $data['id'] = (string)$data['_id'];
//        unset($data['_id']);


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

    public function deleteAll($conditions)
    {
        // TODO: Implement deleteAll() method.
    }

    public function exists($conditions)
    {
        // TODO: Implement exists() method.
    }

    public function save(EntityInterface $entity, $options = [])
    {
        // TODO: Implement save() method.
    }

    public function delete(EntityInterface $entity, $options = [])
    {
        // TODO: Implement delete() method.
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

    public function newEntities(array $data, array $options = [])
    {
        // TODO: Implement newEntities() method.
    }

    public function patchEntity(EntityInterface $entity, array $data, array $options = [])
    {
        // TODO: Implement patchEntity() method.
    }

    public function patchEntities($entities, array $data, array $options = [])
    {
        // TODO: Implement patchEntities() method.
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
     * @return \Dilab\CakeMongo\Marshaller
     */
    public function marshaller()
    {
        return new Marshaller($this);
    }
}