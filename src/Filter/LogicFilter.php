<?php

namespace Imdad\CakeMongo\Filter;

class LogicFilter extends AbstractFilter
{
    private $_logic;

    private $_filters = [];

    /**
     * LogicFilter constructor.
     * @param $_logic
     * @param array ComparisonFilter $_filters
     */
    public function __construct($_logic, array $_filters)
    {
        $this->_logic = $_logic;
        $this->_filters = $_filters;
    }

    public function addFilter(ComparisonFilter $filter)
    {
        $this->_filters[] = $filter;
    }

    public function toArray()
    {
        if ('not' == $this->_logic) {
            $filter = $this->_filters[0];
            $fieldName = $filter->getField();
            return [
                $fieldName => [
                    '$not' => $filter->toArray()[$fieldName],
                ],
            ];
        }

        return [
            '$' . $this->_logic => array_map(function (AbstractFilter $filter) {
                return $filter->toArray();
            }, $this->_filters),
        ];
    }

}
