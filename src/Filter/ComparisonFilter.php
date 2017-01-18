<?php


namespace Dilab\CakeMongo\Filter;


class ComparisonFilter extends AbstractFilter
{
    private $_field;

    private $_operator;

    private $_values;

    /**
     * ComparisonFilter constructor.
     * @param $field
     * @param $operator
     * @param $values
     */
    public function __construct($field, $operator, $values)
    {
        if (!in_array($operator, ['eq', 'lt', 'lte', 'gt', 'gte', 'in', 'nin', 'ne'])) {
            throw new \RuntimeException(sprintf('Invalid operator %s', $operator));
        }

        $this->_field = $field;
        $this->_operator = $operator;
        $this->_values = $values;
    }

    public function toArray()
    {
        if ('eq' == $this->_operator) {
            return [$this->_field => $this->_values];
        }

        return [
            $this->_field => [
                '$' . $this->_operator => $this->_values
            ]
        ];
    }

    /**
     * @return mixed
     */
    public function getField()
    {
        return $this->_field;
    }
}