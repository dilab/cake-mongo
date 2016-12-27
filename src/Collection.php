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
    public function alias($alias = null)
    {
        // TODO: Implement alias() method.
    }

    public function hasField($field)
    {
        // TODO: Implement hasField() method.
    }

    public function find($type = 'all', $options = [])
    {
        // TODO: Implement find() method.
    }

    public function get($primaryKey, $options = [])
    {
        // TODO: Implement get() method.
    }

    public function query()
    {
        // TODO: Implement query() method.
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