<?php

namespace Imdad\CakeMongo\Test\Fixture;

use Imdad\CakeMongo\TestSuite\TestFixture;

/**
 * Profile & Address test fixture.
 */
class ProfilesFixture extends TestFixture
{
    /**
     * The table/type for this fixture.
     *
     * @var string
     */
    public $table = 'profiles';

    /**
     * The mapping data.
     *
     * @var array
     */
    public $schema = [
        'id' => ['type' => 'integer'],
        'username' => ['type' => 'string'],
        'address' => [
            'type' => 'nested',
            'properties' => [
                'street' => ['type' => 'string'],
                'city' => ['type' => 'string'],
                'province' => ['type' => 'string'],
                'country' => ['type' => 'string'],
            ],
        ],
    ];

    /**
     * The fixture records
     *
     * @var array
     */
    public $records = [
        [
            'id' => '507f191e810c19729de860ea',
            'username' => 'mark',
            'address' => [
                'street' => '123 street',
                'city' => 'Toronto',
                'province' => 'Ontario',
                'country' => 'Canada',
            ],
        ],
        [
            'id' => '4e49fd8269fd873c0a000000',
            'username' => 'jose',
            'address' => [
                'street' => '456 street',
                'city' => 'Copenhagen',
                'province' => 'Copenhagen',
                'country' => 'Denmark',
            ],
        ],
        [
            'id' => '4cdfb11e1f3c000000007822',
            'username' => 'sara',
            'address' => [
                [
                    'street' => '456 street',
                    'city' => 'Copenhagen',
                    'province' => 'Copenhagen',
                    'country' => 'Denmark',
                ],
                [
                    'street' => '89 street',
                    'city' => 'Calgary',
                    'province' => 'Alberta',
                    'country' => 'Canada',
                ],
            ],
        ],
    ];
}
