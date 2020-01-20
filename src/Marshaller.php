<?php

namespace Imdad\CakeMongo;

use Cake\Datasource\EntityInterface;

class Marshaller
{

    /**
     * Type instance this marshaller is for.
     *
     * @var \Imdad\CakeMongo\Collection
     */
    protected $collection;

    /**
     * Constructor
     *
     * @param \Imdad\CakeMongo\Collection $collection The collection instance this marshaller is for.
     */
    public function __construct(Collection $collection)
    {
        $this->collection = $collection;
    }

    /**
     * Returns the validation errors for a data set based on the passed options
     *
     * @param array $data The data to validate.
     * @param array $options The options passed to this marshaller.
     * @param bool $isNew Whether it is a new entity or one to be updated.
     * @return array The list of validation errors.
     * @throws \RuntimeException If no validator can be created.
     */
    protected function _validate($data, $options, $isNew)
    {
        if (!$options['validate']) {
            return [];
        }
        if ($options['validate'] === true) {
            $options['validate'] = $this->collection->validator('default');
        }
        if (is_string($options['validate'])) {
            $options['validate'] = $this->collection->validator($options['validate']);
        }
        if (!is_object($options['validate'])) {
            throw new \RuntimeException(
                sprintf('validate must be a boolean, a string or an object. Got %s.', gettype($options['validate']))
            );
        }

        return $options['validate']->errors($data, $isNew);
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
     * @return \Imdad\CakeMongo\Document;
     */
    public function one(array $data, array $options = [])
    {
        $entityClass = $this->collection->entityClass();
        $entity = new $entityClass();
        $entity->source($this->collection->name());
        $options += ['associated' => []];

        list($data, $options) = $this->_prepareDataAndOptions($data, $options);

        if (isset($options['accessibleFields'])) {
            foreach ((array) $options['accessibleFields'] as $key => $value) {
                $entity->accessible($key, $value);
            }
        }
        $errors = $this->_validate($data, $options, true);
        $entity->errors($errors);
        foreach (array_keys($errors) as $badKey) {
            unset($data[$badKey]);
        }

//        foreach ($this->collection->embedded() as $embed) {
        //            $property = $embed->property();
        //            if (in_array($embed->alias(), $options['associated']) &&
        //                isset($data[$property])
        //            ) {
        //                $data[$property] = $this->newNested($embed, $data[$property]);
        //            }
        //        }

        if (!isset($options['fieldList'])) {
            $entity->set($data);

            return $entity;
        }

        foreach ((array) $options['fieldList'] as $field) {
            if (array_key_exists($field, $data)) {
                $entity->set($field, $data[$field]);
            }
        }

        return $entity;
    }

    /**
     * Hydrate a collection of entities.
     *
     * ### Options:
     *
     * * fieldList: A whitelist of fields to be assigned to the entity. If not present,
     *   the accessible fields list in the entity will be used.
     * * accessibleFields: A list of fields to allow or deny in entity accessible fields.
     *
     * @param array $data A list of entity data you want converted into objects.
     * @param array $options Options
     * @return array An array of hydrated entities
     */
    public function many(array $data, array $options = [])
    {
        $output = [];
        foreach ($data as $record) {
            $output[] = $this->one($record, $options);
        }

        return $output;
    }

    /**
     * Merges `$data` into `$document`.
     *
     * ### Options:
     *
     * * fieldList: A whitelist of fields to be assigned to the entity. If not present
     *   the accessible fields list in the entity will be used.
     * * associated: A list of embedded documents you want to marshal.
     *
     * @param \Cake\Datasource\EntityInterface $entity the entity that will get the
     * data merged in
     * @param array $data key value list of fields to be merged into the entity
     * @param array $options List of options.
     * @return \Cake\Datasource\EntityInterface
     */
    public function merge(EntityInterface $entity, array $data, array $options = [])
    {
        $options += ['associated' => []];
        list($data, $options) = $this->_prepareDataAndOptions($data, $options);
        $errors = $this->_validate($data, $options, $entity->isNew());
        $entity->errors($errors);

        foreach (array_keys($errors) as $badKey) {
            unset($data[$badKey]);
        }

//        foreach ($this->collection->embedded() as $embed) {
        //            $property = $embed->property();
        //            if (in_array($embed->alias(), $options['associated']) &&
        //                isset($data[$property])
        //            ) {
        //                $data[$property] = $this->mergeNested($embed, $entity->{$property}, $data[$property]);
        //            }
        //        }

        if (!isset($options['fieldList'])) {
            $entity->set($data);

            return $entity;
        }

        foreach ((array) $options['fieldList'] as $field) {
            if (array_key_exists($field, $data)) {
                $entity->set($field, $data[$field]);
            }
        }

        return $entity;
    }

    /**
     * Update a collection of entities.
     *
     * Merges each of the elements from `$data` into each of the entities in `$entities`.
     *
     * Records in `$data` are matched against the entities using the id field.
     * Entries in `$entities` that cannot be matched to any record in
     * `$data` will be discarded. Records in `$data` that could not be matched will
     * be marshalled as a new entity.
     *
     * ### Options:
     *
     * * fieldList: A whitelist of fields to be assigned to the entity. If not present,
     *   the accessible fields list in the entity will be used.
     *
     * @param array $entities An array of MongoDB entities
     * @param array $data A list of entity data you want converted into objects.
     * @param array $options Options
     * @return array An array of merged entities
     */
    public function mergeMany(array $entities, array $data, array $options = [])
    {
        $indexed = (new \Cake\Collection\Collection($data))
            ->groupBy('id')
            ->map(function ($element, $key) {
                return $key === '' ? $element : $element[0];
            })
            ->toArray();

        $new = isset($indexed[null]) ? $indexed[null] : [];
        unset($indexed[null]);

        $output = [];
        foreach ($entities as $record) {
            if (!($record instanceof EntityInterface)) {
                continue;
            }
            $id = $record->id;
            if (!isset($indexed[$id])) {
                continue;
            }
            $output[] = $this->merge($record, $indexed[$id], $options);
            unset($indexed[$id]);
        }
        $new = array_merge($indexed, $new);
        foreach ($new as $newRecord) {
            $output[] = $this->one($newRecord, $options);
        }

        return $output;
    }

    /**
     * Returns data and options prepared to validate and marshall.
     *
     * @param array $data The data to prepare.
     * @param array $options The options passed to this marshaller.
     * @return array An array containing prepared data and options.
     */
    protected function _prepareDataAndOptions($data, $options)
    {
        $options += ['validate' => true];
        $data = new \ArrayObject($data);
        $options = new \ArrayObject($options);
        $this->collection->dispatchEvent('Model.beforeMarshal', compact('data', 'options'));

        return [(array) $data, (array) $options];
    }
}
