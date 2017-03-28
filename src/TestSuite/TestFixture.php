<?php
namespace Dilab\CakeMongo\TestSuite;

use Cake\Datasource\ConnectionInterface;
use Cake\Datasource\FixtureInterface;
use MongoDB\BSON\ObjectID;

/**
 * A Test fixture implementation for Mongo DB
 *
 * Lets you seed indexes for testing your application.
 *
 * Class extension is temporary as fixtures are missing an interface.
 */
class TestFixture implements FixtureInterface
{

    /**
     * Full Table Name
     *
     * @var string
     */
    public $table = null;

    /**
     * The connection name to use for this fixture.
     *
     * @var string
     */
    public $connection = 'test';

    /**
     * The MongoDB document definition for this document.
     *
     * The schema is not used as MongoDB does not enforce type map
     *
     */
    public $schema = [];

    /**
     * The records to insert.
     *
     * @var array
     */
    public $records = [];

    /**
     * A list of connections this fixtures has been added to.
     *
     * @var array
     */
    public $created = [];

    /**
     * Create the mapping for the document.
     *
     * @param \Cake\Datasource\ConnectionInterface $db The CakeMongo connection
     * @return void
     */
    public function create(ConnectionInterface $db)
    {
        $this->created[] = $db->configName();
    }

    /**
     * Insert fixture documents.
     *
     * @param \Cake\Datasource\ConnectionInterface $db The CakeMongo connection
     * @return void
     */
    public function insert(ConnectionInterface $db)
    {
        if (empty($this->records)) {
            return;
        }

        $documents = [];

        $database = $db->getDatabase();

        $collection = $database->selectCollection($this->table);

        foreach ($this->records as $data) {

            if (isset($data['_id'])) {
                $data['_id'] = new ObjectID($data['_id']);
//                unset($data['id']);
            }

            $documents[] = $data;
        }

        $collection->insertMany($documents);
    }

    /**
     * Drops a database and all its related data.
     *
     * @param \Cake\Datasource\ConnectionInterface $db The CakeMongo connection
     * @return void
     */
    public function drop(ConnectionInterface $db)
    {
        $database = $db->getDatabase();

        $collection = $database->selectCollection($this->table);

        $collection->drop();
    }

    /**
     * Truncate the fixture type.
     *
     * @param \Cake\Datasource\ConnectionInterface $db The CakeMongo connection
     * @return void
     */
    public function truncate(ConnectionInterface $db)
    {
        $database = $db->getDatabase();

        $collection = $database->selectCollection($this->table);

        $collection->drop();
    }

    /**
     * {@inheritDoc}
     */
    public function connection()
    {
        return $this->connection;
    }

    /**
     * {@inheritDoc}
     */
    public function sourceName()
    {
        return $this->table;
    }

    /**
     * No-op method needed because of the Fixture interface.
     * CakeMongo does not deal with foreign key constraints.
     *
     * @param \Cake\Datasource\ConnectionInterface $db The CakeMongo connection
     * @return void
     */
    public function createConstraints(ConnectionInterface $db)
    {
    }

    /**
     * No-op method needed because of the Fixture interface.
     * Elasticsearch does not deal with foreign key constraints.
     *
     * @param \Cake\Datasource\ConnectionInterface $db The CakeMongo connection
     *  connection
     * @return void
     */
    public function dropConstraints(ConnectionInterface $db)
    {
    }
}
