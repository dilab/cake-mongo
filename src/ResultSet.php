<?php

namespace Dilab\CakeMongo;

use Cake\Collection\CollectionTrait;
use Countable;
use IteratorIterator;
use JsonSerializable;
use MongoDB\Driver\Cursor;

/**
 * Decorates the MongoDB Cursor in order to hydrate results with the
 * correct class and provide a Collection interface to the returned results.
 */
class ResultSet extends IteratorIterator implements Countable, JsonSerializable
{
    use CollectionTrait;

    /**
     * Holds the original instance of cursor
     *
     * @var \MongoDB\Driver\Cursor
     */
    protected $cursor;

    /**
     * The full class name of the document class to wrap the results
     *
     * @var \Dilab\CakeMongo\Document
     */
    protected $entityClass;

    /**
     * Name of the collection that the originating query came from.
     *
     * @var string
     */
    protected $repoName;

    /**
     * ResultSet constructor.
     * @param Cursor $cursor
     * @param Query $query
     */
    public function __construct(Cursor $cursor, Query $query)
    {
        $this->cursor = $cursor;

        $repository = $query->repository();

        $this->entityClass = $repository->entityClass();

        $this->repoName = $repository->name();

        parent::__construct($this->cursor);

        parent::rewind();
    }

    /**
     * Returns size of current set
     *
     * @return int Size of set
     */
    public function count()
    {
        return count($this->cursor);
    }

    /**
     * Returns the current document for the iteration
     *
     * @return \Dilab\CakeMongo\Document
     */
    public function current()
    {
        $class = $this->entityClass;
        $result = parent::current();

        $options = [
            'markClean' => true,
            'useSetters' => false,
            'markNew' => false,
            'source' => $this->repoName,
            'result' => $result
        ];

        $data = (array)$result->bsonSerialize();
        $data['id'] = (string)$data['_id'];
        unset($data['_id']);

        $document = new $class($data, $options);
        return $document;
    }
}