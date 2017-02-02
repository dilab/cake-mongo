<?php


namespace Dilab\CakeMongo\Datasource;


use Cake\Datasource\ConnectionInterface;
use MongoDB\Client;

class Connection extends Client implements ConnectionInterface
{
    /**
     * Whether or not query logging is enabled.
     *
     * @var bool
     */
    protected $logQueries = false;

    /**
     * The connection name in the connection manager.
     *
     * @var string
     */
    protected $configName = '';

    /**
     * Constructor. Appends the default database name to the config array, which by default
     * is `test`
     *
     * @param array $config config options
     * @param callback $callback Callback function which can be used to be notified
     * about errors (for example connection down)
     */
    public function __construct(array $config = [], $callback = null)
    {
        if (isset($config['name'])) {
            $this->configName = $config['name'];
        }

        if (isset($config['log'])) {
            $this->logQueries((bool)$config['log']);
        }

        $uri = sprintf('mongodb://%s:%s', $config['host'], $config['port']);

        parent::__construct($uri);
    }

    public function getDatabase($name = null)
    {
        $name = $name ?: 'test';

        return $this->selectDatabase($name);
    }

    /**
     * {@inheritDoc}
     */
    public function configName()
    {
        return $this->configName;
    }

    /**
     * {@inheritDoc}
     */
    public function config()
    {
//        return $this->
    }

    /**
     * {@inheritDoc}
     */
    public function transactional(callable $transaction)
    {
        // TODO: Implement transactional() method.
    }

    /**
     * {@inheritDoc}
     */
    public function disableConstraints(callable $operation)
    {
        // TODO: Implement disableConstraints() method.
    }

    /**
     * {@inheritDoc}
     */
    public function logQueries($enable = null)
    {
        if ($enable === null) {
            return $this->logQueries;
        }

        $this->logQueries = $enable;
    }

    /**
     * {@inheritDoc}
     */
    public function logger($instance = null)
    {
        // TODO: Implement logger() method.
    }


}