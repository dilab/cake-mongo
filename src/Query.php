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
     * Sets the maximum number of results to return for this query.
     * This sets the `limit` option for the MongoDB query.
     *
     * @param int $limit The number of documents to return.
     * @return $this
     */
    public function limit($limit)
    {
        $this->_parts['limit'] = (int)$limit;

        return $this;
    }

    /**
     * Sets the number of records that should be skipped from the original result set
     * This is commonly used for paginating large results. Accepts an integer.
     *
     * @param int $num The number of records to be skipped
     * @return $this
     */
    public function offset($num)
    {
        $this->_parts['offset'] = (int)$num;

        return $this;
    }

    /**
     * Sets the sorting options for the result set.
     *
     * The accepted format for the $order parameter is:
     *
     * - [['name' => ['order'=> 'asc', ...]], ['price' => ['order'=> 'asc', ...]]]
     * - ['name' => 'asc', 'price' => 'desc']
     * - 'field1' (defaults to order => 'desc')
     *
     * @param string|array $order The sorting order to use.
     * @param bool $overwrite Whether or not to replace previous sorting.
     * @return $this
     */
    public function order($order, $overwrite = false)
    {
        // [['field' => [...]], ['field2' => [...]]]
        if (is_array($order) && is_numeric(key($order))) {
            if ($overwrite) {
                $this->_parts['order'] = $order;

                return $this;
            }
            $this->_parts['order'] = array_merge($order, $this->_parts['order']);

            return $this;
        }

        if (is_string($order)) {
            $order = [$order => ['order' => 'desc']];
        }

        $normalizer = function ($order, $key) {
            // ['field' => 'asc|desc']
            if (is_string($order)) {
                return [$key => ['order' => $order]];
            }

            return [$key => $order];
        };

        $order = collection($order)->map($normalizer)->toList();

        if (!$overwrite) {
            $order = array_merge($this->_parts['order'], $order);
        }

        $this->_parts['order'] = $order;

        return $this;
    }

    /**
     * Returns any data that was stored in the specified clause. This is useful for
     * modifying any internal part of the query and it is used during compiling
     * to transform the query accordingly before it is executed. The valid clauses that
     * can be retrieved are: fields, preFilter, postFilter, query, order, limit and offset.
     *
     * The return value for each of those parts may vary. Some clauses use QueryExpression
     * to internally store their state, some use arrays and others may use booleans or
     * integers. This is summary of the return types for each clause.
     *
     * - fields: array, will return empty array when no fields are set
     * - preFilter: The filter to use in a FilteredQuery object, returns null when not set
     * - postFilter: The filter to use in the post_filter object, returns null when not set
     * - query: Raw query (Elastica\Query\AbstractQuery), return null when not set
     * - order: OrderByExpression, returns null when not set
     * - limit: integer, null when not set
     * - offset: integer, null when not set
     *
     * @param string $name name of the clause to be returned
     * @return mixed
     */
    public function clause($name)
    {
        return $this->_parts[$name];
    }

    /**
     * Set the page of results you want.
     *
     * This method provides an easier to use interface to set the limit + offset
     * in the record set you want as results. If empty the limit will default to
     * the existing limit clause, and if that too is empty, then `25` will be used.
     *
     * Pages should start at 1.
     *
     * @param int $num The page number you want.
     * @param int $limit The number of rows you want in the page. If null
     *  the current limit clause will be used.
     * @return $this
     */
    public function page($num, $limit = null)
    {
        if ($limit !== null) {
            $this->limit($limit);
        }
        $limit = $this->clause('limit');
        if ($limit === null) {
            $limit = 25;
            $this->limit($limit);
        }
        $offset = ($num - 1) * $limit;
        if (PHP_INT_MAX <= $offset) {
            $offset = PHP_INT_MAX;
        }
        $this->offset((int)$offset);

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

        if ($this->_parts['limit']) {
            $this->_mongoQuery['limit'] = $this->_parts['limit'];
        }

        if ($this->_parts['offset']) {
            $this->_mongoQuery['skip'] = $this->_parts['offset'];
        }

        if ($this->_parts['order']) {

            $this->_mongoQuery['sort'] = collection($this->_parts['order'])->map(function ($item) {

                $key = key($item);

                $order = $item[$key];

                return [
                    'key' => $key,
                    'order' => ($order['order'] == 'desc' ? -1 : 1)
                ];

            })->reduce(function ($carry, $item) {

                $carry[$item['key']] = $item['order'];

                return $carry;

            }, []);

        }

        return $this->_mongoQuery;
    }


}