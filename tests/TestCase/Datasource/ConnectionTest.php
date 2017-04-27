<?php

namespace Dilab\CakeMongo\Test\TestCase\Datasource;

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
        $database = $connection->getDatabase();
        $this->assertEquals('test', $database->getDatabaseName());

        $connection = new Connection();
        $database = $connection->getDatabase('foobar');
        $this->assertEquals('foobar', $database->getDatabaseName());

        $database = $connection->selectDatabase('baz');
        $this->assertEquals('baz', $database->getDatabaseName());
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
     * Ensure the database option works via the constructor
     *
     * @return void
     */
    public function testConstructDatabaseOption()
    {
        $connection = new Connection();
        $this->assertSame('test',$connection->getDatabase()->getDatabaseName());

        $opts = ['database' => 'cake-mongo'];
        $connection = new Connection($opts);
        $this->assertSame('cake-mongo',$connection->getDatabase()->getDatabaseName());
    }

}
