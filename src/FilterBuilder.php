<?php


namespace Dilab\CakeMongo;

use Dilab\CakeMongo\Filter\ComparisonFilter;
use Dilab\CakeMongo\Filter\LogicFilter;

/**
 * Class FilterBuilder
 * @package Dilab\CakeMongo
 * @see https://docs.mongodb.com/manual/tutorial/query-documents/#specify-query-filter-conditions
 *
 * A query filter is to specify which documents to return.
 * It is implemented based on
 */
class FilterBuilder
{

    /**
     * Matches values that are equal to a specified value.
     *
     * @param $field
     * @param $value
     * @return ComparisonFilter
     */
    public function eq($field, $value)
    {
        return $this->_comparison('eq', $field, $value);
    }

    /**
     * Matches values that are greater than a specified value.
     *
     * @param $field
     * @param $value
     * @return ComparisonFilter
     */
    public function gt($field, $value)
    {
        return $this->_comparison('gt', $field, $value);
    }

    /**
     * Matches values that are greater than or equal to a specified value.
     *
     * @param $field
     * @param $value
     * @return ComparisonFilter
     */
    public function gte($field, $value)
    {
        return $this->_comparison('gte', $field, $value);
    }

    /**
     * Matches values that are less than a specified value.
     *
     * @param $field
     * @param $value
     * @return ComparisonFilter
     */
    public function lt($field, $value)
    {
        return $this->_comparison('lt', $field, $value);
    }

    /**
     * Matches values that are less than or equal to a specified value.
     *
     * @param $field
     * @param $value
     * @return ComparisonFilter
     */
    public function lte($field, $value)
    {
        return $this->_comparison('lte', $field, $value);
    }

    /**
     * Matches all values that are not equal to a specified value.
     *
     * @param $field
     * @param $value
     * @return ComparisonFilter
     */
    public function ne($field, $value)
    {
        return $this->_comparison('ne', $field, $value);
    }

    /**
     * Matches any of the values specified in an array.
     *
     * @param $field
     * @param $value
     * @return ComparisonFilter
     */
    public function in($field, $value)
    {
        if (!is_array($value)) {
            $value = [$value];
        }

        return $this->_comparison('in', $field, $value);
    }

    /**
     * Matches none of the values specified in an array.
     *
     * @param $field
     * @param $value
     * @return ComparisonFilter
     */
    public function nin($field, $value)
    {
        if (!is_array($value)) {
            $value = [$value];
        }

        return $this->_comparison('nin', $field, $value);
    }

    /**
     *
     * Helper for forming comparison filter
     *
     * @param $operator
     * @param $field
     * @param $value
     * @return ComparisonFilter
     */
    private function _comparison($operator, $field, $value)
    {
        return new ComparisonFilter($field, $operator, $value);
    }

    /**
     * Combines all the passed arguments in a single filter.
     *
     * ### Example:
     *
     * {{{
     * $result = $builder->or(
     *      $builder->lt('quantity', 20),
     *      $builder->eq('price', 10)
     * );
     * }}}
     *
     * @return LogicFilter
     */
    // @codingStandardsIgnoreStart
    public function or_()
    {
        // @codingStandardsIgnoreEnd
        $filters = func_get_args();

        $orFilter = new LogicFilter('or', []);

        foreach ($filters as $filter) {
            $orFilter->addFilter($filter);
        }

        return $orFilter;
    }

    /**
     * Combines all the passed arguments in a single filter.
     *
     * ### Example:
     *
     * {{{
     * $result = $builder->and(
     *      $builder->lt('quantity', 20),
     *      $builder->eq('price', 10)
     * );
     * }}}
     *
     * @return LogicFilter
     */
    // @codingStandardsIgnoreStart
    public function and_()
    {
        // @codingStandardsIgnoreEnd
        $filters = func_get_args();

        $andFilter = new LogicFilter('and', []);

        foreach ($filters as $filter) {
            $andFilter->addFilter($filter);
        }

        return $andFilter;
    }

    /**
     * Helps calling the `and()` and `or()` methods transparently.
     *
     * @param string $method The method name.
     * @param array $args The argumemts to pass to the method.
     * @return array
     */
    public function __call($method, $args)
    {
        if (in_array($method, ['and', 'or'])) {
            return call_user_func_array([$this, $method . '_'], $args);
        }
        throw new \BadMethodCallException('Cannot build filter ' . $method);
    }

    /**
     * Returns a filter that is typically used to negate another filter expression
     *
     * @param array $filter The filter to negate
     * @return LogicFilter
     */
    public function not($filter)
    {
        return new LogicFilter('not', [$filter]);
    }

    /**
     * Returns a filter that is a logical NOR returns all documents that fail to match both clauses.
     *
     * @param array $filters
     * @return LogicFilter
     */
    // @codingStandardsIgnoreStart
    public function nor()
    {
        // @codingStandardsIgnoreEnd
        $filters = func_get_args();

        $norFilter =new LogicFilter('nor', $filters);

        foreach ($filters as $filter) {
            $norFilter->addFilter($filter);
        }

        return $norFilter;
    }
}