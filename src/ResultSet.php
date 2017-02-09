<?php


namespace Dilab\CakeMongo;


class ResultSet extends \IteratorIterator implements \Countable, \JsonSerializable
{
    /**
     * Decorator's constructor
     *
     * @param \MongoDB\Driver\Cursor $resultSet The results from MongoDB to wrap
     * @param array $query The Filter array
     */
    public function __construct($resultSet, $query)
    {
//        $this->resultSet = $resultSet;
//        $repo = $query->repository();
//        foreach ($repo->embedded() as $embed) {
//            $this->embeds[$embed->property()] = $embed;
//        }
//        $this->entityClass = $repo->entityClass();
//        $this->repoName = $repo->name();
        parent::__construct($resultSet);
    }

    public function count()
    {
        // TODO: Implement count() method.
    }

    function jsonSerialize()
    {
        // TODO: Implement jsonSerialize() method.
    }


}