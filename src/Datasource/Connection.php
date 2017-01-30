<?php


namespace Dilab\CakeMongo\Datasource;


use Cake\Datasource\ConnectionInterface;

class Connection implements ConnectionInterface
{
    public function configName()
    {
        // TODO: Implement configName() method.
    }

    public function config()
    {
        // TODO: Implement config() method.
    }

    public function transactional(callable $transaction)
    {
        // TODO: Implement transactional() method.
    }

    public function disableConstraints(callable $operation)
    {
        // TODO: Implement disableConstraints() method.
    }

    public function logQueries($enable = null)
    {
        // TODO: Implement logQueries() method.
    }

    public function logger($instance = null)
    {
        // TODO: Implement logger() method.
    }


}