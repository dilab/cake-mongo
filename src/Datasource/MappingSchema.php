<?php
namespace Imdad\CakeMongo\Datasource;

/**
 * Object interface for MongoDB mapping information.
 */
class MappingSchema
{
    /**
     * The raw mapping data from CakeMongo
     *
     * @var array
     */
    protected $data;

    /**
     * The name of the type this mapping data is for.
     *
     * @var string
     */
    protected $name;

    /**
     * Constructor
     *
     * @param string $name The name of the type of the mapping data
     * @param array $data The mapping data from CakeMongo
     */
    public function __construct($name, array $data)
    {
        $this->name = $name;
//        if (isset($data[$name]['properties'])) {
        //            $data = $data[$name]['properties'];
        //        }
        //        $this->data = $data;
    }

    /**
     * Get the name of the type for this mapping.
     *
     * @return string
     */
    public function name()
    {
        return $this->name;
    }

    /**
     * Get the mapping information for a single field.
     *
     * Can access nested fields through dot paths.
     *
     * @param string $name The path to the field you want.
     * @return array|null Either field mapping data or null.
     */
    public function field($name)
    {
        return 'flexible';
//        if (strpos($name, '.') === false) {
        //            if (isset($this->data[$name])) {
        //                return $this->data[$name];
        //            }
        //
        //            return null;
        //        }
        //        $parts = explode('.', $name);
        //        $pointer = $this->data;
        //        foreach ($parts as $part) {
        //            if (isset($pointer[$part]['type']) && $pointer[$part]['type'] !== 'nested') {
        //                return $pointer[$part];
        //            }
        //            if (isset($pointer[$part]['properties'])) {
        //                $pointer = $pointer[$part]['properties'];
        //            }
        //        }
    }

    /**
     * Get the field type for a field.
     *
     * Can access nested fields through dot paths.
     *
     * @param string $name The path to the field you want.
     * @return string|null Either type information or null
     */
    public function fieldType($name)
    {
        return 'flexible';

//        $field = $this->field($name);
        //        if (!$field) {
        //            return null;
        //        }
        //
        //        return $field['type'];
    }

    /**
     * Get the field names in the mapping.
     *
     * Will only return the top level fields. Nested object field names will
     * not be included.
     *
     * @return array
     */
    public function fields()
    {
        return array_keys($this->data);
    }
}
