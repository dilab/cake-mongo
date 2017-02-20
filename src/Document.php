<?php


namespace Dilab\CakeMongo;
use Cake\Datasource\EntityInterface;
use Cake\Datasource\EntityTrait;

/**
 * Represents a document stored in a MongoDB collection
 * A document in MongoDb is approximately equivalent to a row
 * in a relational datastore.
 */
class Document implements EntityInterface
{
    use EntityTrait;


}