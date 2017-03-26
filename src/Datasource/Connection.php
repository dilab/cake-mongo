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
     * @var Hold config values
     */
    private $_config;

    /**
     * Constructor. Appends the default database name to the config array,
     * which by default is `test`
     *
     * @param array $config config options
     * @param callback $callback Callback function which can be used to be notified
     * about errors (for example connection down)
     */
    public function __construct(array $config = [], $callback = null)
    {
        $config = array_merge(['host' => '127.0.0.1', 'port' => '27017'], $config);

        if (isset($config['name'])) {
            $this->configName = $config['name'];
        }

        if (isset($config['log'])) {
            $this->logQueries((bool)$config['log']);
        }

        $uri = sprintf('mongodb://%s:%s', $config['host'], $config['port']);

        $this->setConfig($config);
        parent::__construct($uri);
    }

    /**
     * @param null $name
     * @return \MongoDB\Database
     */
    public function getDatabase($name = null)
    {
        if (null == $name) {
            $name = isset($this->_config['database']) ? $this->_config['database'] : 'test';
        }
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
        return $this->_config;
    }

    private function setConfig(array $config)
    {
        foreach ($config as $key => $value) {
            $this->_config[$key] = $value;
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function transactional(callable $transaction)
    {
        return $transaction($this);
    }

    /**
     * {@inheritDoc}
     */
    public function disableConstraints(callable $operation)
    {
        return $operation($this);
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
     * {@inheritDoccl}
     */
    public function logger($instance = null)
    {
        if ($instance === null) {
            return $this->_logger;
        }
        $this->_logger = $instance;
    }

    /**
     * Returns a SchemaCollection stub until we can add more
     * abstract API's in Connection.
     *
     * @return \Dilab\CakeMongo\Datasource\SchemaCollection
     */
    public function schemaCollection()
    {
        return new SchemaCollection($this);
    }

}