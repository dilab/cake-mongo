<?php


namespace Dilab\CakeMongo;

use Cake\Datasource\EntityInterface;
use Cake\Datasource\EntityTrait;
use MongoDB\Model\BSONDocument;

/**
 * Represents a document stored in a MongoDB collection
 * A document in MongoDb is approximately equivalent to a row
 * in a relational datastore.
 */
class Document implements EntityInterface
{
    use EntityTrait;

    /**
     * Takes either an array or a Result object form a search and constructs
     * a document representing an entity in a elastic search type,
     *
     * @param array|BSONDocument $data An array or BSONDocument that
     *  represents an document
     * @param array $options An array of options to set the state of the
     *  document
     */
    public function __construct($data = [], $options = [])
    {
        if ($data instanceof BSONDocument) {
            $data = (array)$data->bsonSerialize();
            $data['_id'] = (string)$data['_id'];
//            unset($data['_id']);
        }

        $options += [
            'useSetters' => true,
            'markClean' => false,
            'markNew' => null,
            'guard' => false,
            'source' => null,
            'result' => null
        ];

        if (!empty($options['source'])) {
            $this->setSource($options['source']);
        }

        if ($options['markNew'] !== null) {
            $this->isNew($options['markNew']);
        }

        if ($options['result'] !== null) {
            $this->_result = $options['result'];
        }

        if ($options['markClean']) {
            $this->clean();
        }

        if (!empty($data) && $options['markClean'] && !$options['useSetters']) {

            $this->_properties = $data;

            return;
        }

        if (!empty($data)) {
            $this->set($data, [
                'setter' => $options['useSetters'],
                'guard' => $options['guard']
            ]);
        }
    }

}