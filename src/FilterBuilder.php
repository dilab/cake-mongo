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
        return [
            $field => [
                '$gt' => $value
            ]
        ];
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
        return [
            $field => [
                '$gte' => $value
            ]
        ];
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
        return [
            $field => [
                '$lt' => $value
            ]
        ];
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
        return [
            $field => [
                '$lte' => $value
            ]
        ];
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
        return [
            $field => [
                '$ne' => $value
            ]
        ];
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

        return [
            $field => [
                '$in' => $value
            ]
        ];
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

        return [
            $field => [
                '$nin' => $value
            ]
        ];
    }


}