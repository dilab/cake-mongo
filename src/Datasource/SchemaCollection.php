<?php

namespace Imdad\CakeMongo\Datasource;

/**
 * Temporary shim for fixtures as they know too much about databases.
 */
class SchemaCollection
{
    /**
     * The connection instance to use.
     *
     * @var \Imdad\CakeMongo\Datasource\Connection
     */
    protected $connection;

    /**
     * Constructor
     *
     * @param \Imdad\CakeMongo\Datasource\Connection $connection The connection instance to use.
     */
    public function __construct($connection)
    {
        $this->connection = $connection;
    }

    /**
     * Returns an empty array as a shim for fixtures
     *
     * @return array An empty array
     */
    public function listTables()
    {
        try {
            $database = $this->connection->getDatabase();
            $collections = $database->listCollections();
            return iterator_to_array($collections);
        } catch (\Exception $e) {
            return [];
        }

    }
}
