<?php


namespace Dilab\CakeMongo;


class Marshaller
{

    /**
     * Type instance this marshaller is for.
     *
     * @var \Dilab\CakeMongo\Collection
     */
    protected $collection;

    /**
     * Constructor
     *
     * @param \Dilab\CakeMongo\Collection $collection The collection instance this marshaller is for.
     */
    public function __construct(Collection $collection)
    {
        $this->collection = $collection;
    }

    /**
     * Hydrate a single document.
     *
     * ### Options:
     *
     * * fieldList: A whitelist of fields to be assigned to the entity. If not present,
     *   the accessible fields list in the entity will be used.
     * * accessibleFields: A list of fields to allow or deny in entity accessible fields.
     * * associated: A list of embedded documents you want to marshal.
     *
     * @param array $data The data to hydrate.
     * @param array $options List of options
     * @return \Dilab\CakeMongo\Document;
     */
    public function one(array $data, array $options = [])
    {
        $entityClass = $this->collection->entityClass();
        $entity = new $entityClass();
        $entity->source($this->collection->name());
        $options += ['associated' => []];

        list($data, $options) = $this->_prepareDataAndOptions($data, $options);

        if (isset($options['accessibleFields'])) {
            foreach ((array)$options['accessibleFields'] as $key => $value) {
                $entity->accessible($key, $value);
            }
        }
        $errors = $this->_validate($data, $options, true);
        $entity->errors($errors);
        foreach (array_keys($errors) as $badKey) {
            unset($data[$badKey]);
        }

        foreach ($this->type->embedded() as $embed) {
            $property = $embed->property();
            if (in_array($embed->alias(), $options['associated']) &&
                isset($data[$property])
            ) {
                $data[$property] = $this->newNested($embed, $data[$property]);
            }
        }

        if (!isset($options['fieldList'])) {
            $entity->set($data);

            return $entity;
        }

        foreach ((array)$options['fieldList'] as $field) {
            if (array_key_exists($field, $data)) {
                $entity->set($field, $data[$field]);
            }
        }

        return $entity;
    }
}