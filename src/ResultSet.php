<?php

namespace Dilab\CakeMongo;

use Cake\Collection\CollectionTrait;
use Countable;
use IteratorIterator;
use JsonSerializable;

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
     * Decorator's constructor
     *
     * @param \MongoDB\Driver\Cursor $cursor The cursor from MongoDB to wrap
     * @param array $query The Filter array
     */
    public function __construct($cursor, $query)
    {
        parent::__construct($cursor);
    }

    public function count()
    {
        // TODO: Implement count() method.
    }

    /**
     * Returns the current document for the iteration
     *
     * @return \Dilab\CakeMongo\Document
     */
    public function current()
    {

    }

    function jsonSerialize()
    {
        // TODO: Implement jsonSerialize() method.
    }


}