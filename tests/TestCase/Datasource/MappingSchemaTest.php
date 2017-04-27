<?php

namespace Dilab\CakeMongo\Test\TestCase\Datasource;

use Cake\ElasticSearch\Datasource\MappingSchema;
use Cake\TestSuite\TestCase;
use Cake\Validation\Validator;

/**
 * Test case for the MappingSchema
 */
class MappingSchemaTest extends TestCase
{
    /**
     * Test the name()
     *
     * @return void
     */
    public function testName()
    {
        $this->markTestIncomplete('TODO');

        $mapping = new MappingSchema('articles', []);
        $this->assertEquals('articles', $mapping->name());
    }

    /**
     * Test fields()
     *
     * @return void
     */
    public function testFields()
    {
        $this->markTestIncomplete('TODO');

        $data = [
            'user_id' => [
                'type' => 'integer'
            ],
            'title' => [
                'type' => 'string'
            ],
            'body' => [
                'type' => 'string'
            ]
        ];
        $mapping = new MappingSchema('articles', $data);
        $expected = array_keys($data);
        $this->assertEquals($expected, $mapping->fields());
    }

    /**
     * Test field()
     *
     * @return void
     */
    public function testField()
    {
        $this->markTestIncomplete('TODO');

        $data = [
            'user_id' => [
                'type' => 'integer'
            ],
            'title' => [
                'type' => 'string',
                'null_value' => 'na',
            ],
            'body' => [
                'type' => 'string'
            ]
        ];
        $mapping = new MappingSchema('articles', $data);
        $this->assertEquals($data['user_id'], $mapping->field('user_id'));
        $this->assertEquals($data['title'], $mapping->field('title'));
        $this->assertNull($mapping->field('nope'));
    }

    /**
     * Test field()
     *
     * @return void
     */
    public function testFieldNested()
    {
        $this->markTestIncomplete('TODO');
        $data = [
            'user_id' => [
                'type' => 'integer'
            ],
            'address' => [
                'type' => 'nested',
                'properties' => [
                    'street' => ['type' => 'string'],
                ]
            ],
        ];
        $mapping = new MappingSchema('articles', $data);
        $this->assertEquals(['type' => 'string'], $mapping->field('address.street'));
        $this->assertNull($mapping->field('address.nope'));
    }

    /**
     * Test fieldType()
     *
     * @return void
     */
    public function testFieldType()
    {
        $this->markTestIncomplete('TODO');

        $data = [
            'user_id' => [
                'type' => 'integer'
            ],
            'address' => [
                'type' => 'nested',
                'properties' => [
                    'street' => ['type' => 'string'],
                ]
            ],
        ];
        $mapping = new MappingSchema('articles', $data);
        $this->assertEquals('integer', $mapping->fieldType('user_id'));
        $this->assertEquals('string', $mapping->fieldType('address.street'));
        $this->assertNull($mapping->fieldType('address.nope'));
    }
}
