<?php


namespace Dilab\CakeMongo;

use Dilab\CakeMongo\Filter\ComparisonFilter;
use Dilab\CakeMongo\Filter\ElementFilter;
use Dilab\CakeMongo\Filter\LogicFilter;

/**
 * Class FilterBuilder
 * @package Dilab\CakeMongo
 * @see https://docs.mongodb.com/manual/tutorial/query-documents/#specify-query-filter-conditions
 *      https://docs.mongodb.com/manual/reference/operator/query/#query-and-projection-operators
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

        return $this->_logic('or', $filters);
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

        return $this->_logic('and', $filters);
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
     * @return LogicFilter
     */
    // @codingStandardsIgnoreStart
    public function nor()
    {
        // @codingStandardsIgnoreEnd
        $filters = func_get_args();

        return $this->_logic('nor', $filters);
    }

    /**
     * Helps to build logic operator
     *
     * @param $operator
     * @param $filters
     * @return LogicFilter
     */
    private function _logic($operator, $filters)
    {
        $norFilter = new LogicFilter($operator, $filters);

        return $norFilter;
    }

    /**
     *
     * Return a filter that matches documents that have the specified field.
     *
     * @param $field
     * @return ElementFilter
     */
    public function exists($field)
    {
        return new ElementFilter('exists', $field);
    }

    /**
     *
     * Return a filter that matches documents that do not the specified field.
     *
     * @param $field
     * @return ElementFilter
     */
    public function missing($field)
    {
        return new ElementFilter('missing', $field);
    }

    /**
     * Converts an array into a single array of filter objects
     *
     * ### Parsing a single array:
     *
     *   {{{
     *       $filter = $builder->parse([
     *           'name' => 'mark',
     *           'age <=' => 35
     *       ]);
     *
     *       // Equivalent to:
     *       $filter = [
     *           $builder->eq('name', 'mark'),
     *           $builder->lte('age', 35)
     *       ];
     *   }}}
     *
     * ### Creating "or" conditions:
     *
     * {{{
     *  $filter = $builder->parse([
     *      'or' => [
     *          'name' => 'mark',
     *          'age <=' => 35
     *      ]
     *  ]);
     *
     *  // Equivalent to:
     *  $filter = [$builder->or(
     *      $builder->eq('name', 'mark'),
     *      $builder->lte('age', 35)
     *  )];
     * }}}
     *
     * ### Negating conditions:
     *
     * {{{
     *  $filter = $builder->parse([
     *      'not' => [
     *          'name' => 'mark',
     *      ]
     *  ]);
     *
     *  // Equivalent to:
     *  $filter = [$builder->not(
     *          $builder->eq('name', 'mark'),
     *  )];
     * }}}
     *
     *
     * ### Checking if a value is in a list of terms
     *
     * {{{
     *       $filter = $builder->parse([
     *           'name in' => ['jose', 'mark']
     *       ]);
     *
     *       // Equivalent to:
     *       $filter = [$builder->in('name', ['jose', 'mark'])]
     * }}}
     *
     * The list of supported operators is:
     *
     * `<`, `>`, `<=`, `>=`, `in`, `not in`, `!=`
     *
     * @param array|\Dilab\CakeMongo\Filter\AbstractFilter $conditions The list of conditions to parse.
     * @return array
     */
    public function parse($conditions)
    {

    }


}