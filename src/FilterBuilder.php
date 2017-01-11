<?php


namespace Dilab\CakeMongo;

/**
 * Class FilterBuilder
 * @package Dilab\CakeMongo
 *
 * A query filter is to specify which documents to return.
 * It is implemented based on
 * https://docs.mongodb.com/manual/tutorial/query-documents/#specify-query-filter-conditions
 */
class FilterBuilder
{

    /**
     * Matches values that are equal to a specified value.
     *
     * @param $field
     * @param $value
     * @return array
     */
    public function eq($field, $value)
    {
        return [$field => $value];
    }

    /**
     * Matches values that are greater than a specified value.
     *
     * @param $field
     * @param $value
     * @return array
     */
    public function gt($field, $value)
    {
        return $this->_comparison('$gt', $field, $value);
    }

    /**
     * Matches values that are greater than or equal to a specified value.
     *
     * @param $field
     * @param $value
     * @return array
     */
    public function gte($field, $value)
    {
        return $this->_comparison('$gte', $field, $value);
    }

    /**
     * Matches values that are less than a specified value.
     *
     * @param $field
     * @param $value
     * @return array
     */
    public function lt($field, $value)
    {
        return $this->_comparison('$lt', $field, $value);
    }

    /**
     * Matches values that are less than or equal to a specified value.
     *
     * @param $field
     * @param $value
     * @return array
     */
    public function lte($field, $value)
    {
        return $this->_comparison('$lte', $field, $value);
    }

    /**
     * Matches all values that are not equal to a specified value.
     *
     * @param $field
     * @param $value
     * @return array
     */
    public function ne($field, $value)
    {
        return $this->_comparison('$ne', $field, $value);
    }

    /**
     * Matches any of the values specified in an array.
     *
     * @param $field
     * @param $value
     * @return array
     */
    public function in($field, $value)
    {
        if (!is_array($value)) {
            $value = [$value];
        }

        return $this->_comparison('$in', $field, $value);
    }

    /**
     * Matches none of the values specified in an array.
     *
     * @param $field
     * @param $value
     * @return array
     */
    public function nin($field, $value)
    {
        if (!is_array($value)) {
            $value = [$value];
        }

        return $this->_comparison('$nin', $field, $value);
    }

    /**
     *
     * Helper for forming comparison filter
     *
     * @param $operator
     * @param $field
     * @param $value
     * @return array
     */
    private function _comparison($operator, $field, $value)
    {
        return [$field => [$operator => $value]];
    }

}