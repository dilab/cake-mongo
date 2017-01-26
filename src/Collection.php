<?php


namespace Dilab\CakeMongo;

use Cake\Datasource\EntityInterface;
use Cake\Datasource\RepositoryInterface;

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

    public function alias($alias = null)
    {
        // TODO: Implement alias() method.
    }

    public function hasField($field)
    {
        // TODO: Implement hasField() method.
    }

    public function get($primaryKey, $options = [])
    {
        // TODO: Implement get() method.
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

    public function newEntity($data = null, array $options = [])
    {
        // TODO: Implement newEntity() method.
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

}