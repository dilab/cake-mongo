<?php

namespace Imdad\CakeMongo\Filter;

class ElementFilter extends AbstractFilter
{
    private $_type;

    private $_field;

    /**
     * ElementFilter constructor.
     * @param $type
     * @param $field
     */
    public function __construct($type, $field)
    {
        $this->_type = $type;
        $this->_field = $field;
    }

    public function toArray()
    {
        if ('exists' == $this->_type) {

            return [
                $this->_field => [
                    '$exists' => true,
                ],
            ];

        }

        if ('missing' == $this->_type) {

            return [
                $this->_field => [
                    '$exists' => false,
                ],
            ];

        }
    }

}
