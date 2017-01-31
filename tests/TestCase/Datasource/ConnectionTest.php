<?php


use Cake\TestSuite\TestCase;
use Dilab\CakeMongo\Datasource\Connection;

class ConnectionTest extends TestCase
{
    /**
     * Tests the getDatabase method, in particular, that calling it with no arguments
     * will use the default database for the connection
     *
     * @return void
     */
    public function testGetDatabase()
    {
        $connection = new Connection();
        $index = $connection->getDatabase();
        $this->assertEquals('test', $index->getDatabaseName());

        $connection = new Connection(['database' => 'foobar']);
        $index = $connection->getDatabase();
        $this->assertEquals('foobar', $index->getDatabaseName());

        $index = $connection->selectDatabase('baz');
        $this->assertEquals('baz', $index->getDatabaseName());
    }

    /**
     * Ensure the log option works via the constructor
     *
     * @return void
     */
    public function testConstructLogOption()
    {
        $connection = new Connection();
        $this->assertFalse($connection->logQueries());

        $opts = ['log' => true];
        $connection = new Connection($opts);
        $this->assertTrue($connection->logQueries());
    }

    /**
     * Ensure that logging queries works.
     *
     * @return void
     */
    public function testQueryLogging()
    {
        $logger = $this->getMock('Cake\Log\Engine\BaseLog', ['log']);
        $logger->expects($this->once())->method('log');
        Log::config('cakemongo', $logger);

        $connection = ConnectionManager::get('test');
        $connection->logQueries(true);
        $result = $connection->request('_stats');
        $connection->logQueries(false);

        $this->assertNotEmpty($result);
    }
}
