<?php


namespace Dilab\CakeMongo;

use Cake\Datasource\QueryTrait;
use Dilab\CakeMongo\Filter\AbstractFilter;
use IteratorAggregate;

/**
 * Class Query
 * @package Dilab\CakeMongo
 *
 */
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
        'filter' => [],
        'order' => [],
        'limit' => null,
        'offset' => null
    ];

    protected $_mongoQuery = [];

    /**
     * Query constructor.
     * @param \Dilab\CakeMongo\Collection $repository
     */
    public function __construct(Collection $repository)
    {
        $this->repository($repository);
    }

    /**
     * Populates or adds parts to current query clauses using an array.
     * This is handy for passing all query clauses at once. The option array accepts:
     *
     * - fields: Maps to the select method
     * - conditions: Maps to the where method
     * - order: Maps to the order method
     * - limit: Maps to the limit method
     * - offset: Maps to the offset method
     * - page: Maps to the page method
     *
     * ### Example:
     *
     * ```
     * $query->applyOptions([
     *   'fields' => ['id', 'name'],
     *   'conditions' => [
     *     'created >=' => '2013-01-01'
     *   ],
     *   'limit' => 10
     * ]);
     * ```
     *
     * Is equivalent to:
     *
     * ```
     *  $query
     *  ->select(['id', 'name'])
     *  ->where(['created >=' => '2013-01-01'])
     *  ->limit(10)
     * ```
     *
     * @param array $options list of query clauses to apply new parts to.
     * @return $this
     */
    public function applyOptions(array $options)
    {
        $valid = [
            'fields' => 'select',
            'conditions' => 'where',
            'order' => 'order',
            'limit' => 'limit',
            'offset' => 'offset',
            'page' => 'page',
        ];

        ksort($options);

        foreach ($options as $option => $values) {
            if (isset($valid[$option]) && isset($values)) {
                $this->{$valid[$option]}($values);
            } else {
                $this->_options[$option] = $values;
            }
        }

        return $this;
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

        if ($this->_parts['filter']) {
            $this->_mongoQuery['filter'] = array_map(function (AbstractFilter $filter) {
                return $filter->toArray();
            }, $this->_parts['filter']);
        }

        return $this->_mongoQuery;
    }

    /**
     * Sets the filter to use in a Query object. Filters added using this method
     * will be applied to the filter part of a query.
     *
     * There are several way in which you can use this method. The easiest one is by passing
     * a simple array of conditions:
     *
     * {{{
     *   // Generates a {"name": "jose"} filter
     *   $query->where(['name' => 'jose']);
     * }}}
     *
     * You can have as many conditions in the array as you'd like, Operators are also allowed in
     * the field side of the array:
     *
     * {{{
     *   $query->where(['name' => 'jose', 'age >' => 30, 'interests in' => ['php', 'cake']);
     * }}}
     *
     * You can read about the available operators and how they translate to MongoDB
     * filters in the `Dilab\CakeMongo\FilterBuilder::parse()` method documentation.
     *
     * Additionally, it is possible to use a closure as first argument. The closure will receive
     * a FilterBuilder instance, that you can use for creating arbitrary filter combinations:
     *
     * {{{
     *   $query->where(function ($builder) {
     *    return $builder->and($builder->gt('age', 10), $builder->in(['name']));
     *   });
     * }}}
     *
     * Finally, you can pass any already built filters as first argument:
     *
     * {{{
     *   $query->where(new \Dilab\CakeMongo\ComparisionFilter('price', 'gte', 10));
     * }}{
     *
     * @param array|callable|\Dilab\CakeMongo\AbstractFilter $conditions The list of conditions.
     * @param bool $overwrite Whether or not to replace previous filters.
     * @return $this
     * @see \Dilab\CakeMongo\FilterBuilder
     */
    public function where($conditions, $overwrite = false)
    {
        if (is_callable($conditions)) {
            $conditions = [call_user_func($conditions, new FilterBuilder)];
        }

        if ($overwrite) {
            $this->_parts['filter'] = (new FilterBuilder)->parse($conditions);
        }

        $this->_parts['filter'] =
            array_merge($this->_parts['filter'], (new FilterBuilder)->parse($conditions));
    }

    /**
     * {@inheritDoc}
     *
     * @return \Dilab\CakeMongo\Query
     */
    public function find($type = 'all', $options = [])
    {
        return $this->_repository->callFinder($type, $this, $options);
    }

    /**
     * Executes the query.
     *
     * @return \Dilab\CakeMongo\ResultSet The results of the query
     */
    protected function _execute()
    {
        $connection = $this->_repository->connection();

        $name = $this->_repository->name();

        $collection = $connection->getIndex()->getCollection($name);

        $query = $this->compileQuery();

        return new ResultSet($collection->search($query, $this->_searchOptions), $this);
    }
}