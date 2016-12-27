<?php


namespace Dilab\CakeMongo;
use Cake\Datasource\EntityInterface;
use Cake\Datasource\EntityTrait;

/**
 * Represents a document stored in a MongoDB collection
 *
 */
class Document implements EntityInterface
{
    use EntityTrait;

}