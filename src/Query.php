<?php


namespace Dilab\CakeMongo;

use Cake\Datasource\QueryTrait;
use IteratorAggregate;

class Query implements IteratorAggregate
{
    use QueryTrait;

    /**
     * The various query builder parts that will
     * be transferred to the MongoDB query.
     *
     * @var array
     */
    protected $_parts = [
        'fields' => [],
        'preFilter' => null,
        'postFilter' => null,
        'highlight' => null,
        'query' => null,
        'order' => [],
        'limit' => null,
        'offset' => null,
        'aggregations' => []
    ];

    protected $_mongoQuery = [
        'projection' => []
    ];

    /**
     * Query constructor.
     * @param \Dilab\CakeMongo\Collection $repository
     */
    public function __construct(Collection $repository)
    {
        $this->repository($repository);
    }

    public function applyOptions(array $options)
    {
        // TODO: Implement applyOptions() method.
    }

    protected function _execute()
    {
        // TODO: Implement _execute() method.
    }

    /**
     * Adds fields to be selected from _source.
     *
     * Calling this function multiple times will append more fields to the
     * list of fields to be selected from _source.
     *
     * If `true` is passed in the second argument, any previous selections
     * will be overwritten with the list passed in the first argument.
     *
     * @param array $fields The list of fields to select from _source.
     * @param bool $overwrite Whether or not to replace previous selections.
     * @return $this
     */
    public function select(array $fields, $overwrite = false)
    {
        if (!$overwrite) {
            $fields = array_merge($this->_parts['fields'], $fields);
        }
        $this->_parts['fields'] = $fields;

        return $this;
    }

    /**
     * Compile the MongoDB query.
     *
     * @return string The MongoDB query.
     */
    public function compileQuery()
    {
        if ($this->_parts['fields']) {
            $this->_mongoQuery['projection'] =
                array_combine($this->_parts['fields'], array_fill(0, count($this->_parts['fields']), 1));
        }

        return $this->_mongoQuery;
    }

}