<?php


namespace Dilab\CakeMongo;


use Cake\TestSuite\TestCase;

class FilterBuilderTest extends TestCase
{

    /**
     * Tests the eq() filter
     *
     * @return void
     */
    public function testEq()
    {
        $builder = new FilterBuilder();
        $result = $builder->eq('price', 10)->toArray();
        $expected = [
            'price' => 10
        ];
        $this->assertEquals($expected, $result);

        $result = $builder->eq('year', '2014')->toArray();
        $expected = [
            'year' => 2014
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * Tests the gt() filter
     *
     * @return void
     */
    public function testGt()
    {
        $builder = new FilterBuilder();
        $result = $builder->gt('price', 10)->toArray();
        $expected = [
            'price' => ['$gt' => 10]
        ];
        $this->assertEquals($expected, $result);

        $result = $builder->gt('year', '2014')->toArray();
        $expected = [
            'year' => ['$gt' => 2014]
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * Tests the gte() filter
     *
     * @return void
     */
    public function testGte()
    {
        $builder = new FilterBuilder();
        $result = $builder->gte('price', 10)->toArray();
        $expected = [
            'price' => ['$gte' => 10]
        ];
        $this->assertEquals($expected, $result);

        $result = $builder->gte('year', '2014')->toArray();
        $expected = [
            'year' => ['$gte' => 2014]
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * Tests the lt() filter
     *
     * @return void
     */
    public function testLt()
    {
        $builder = new FilterBuilder();
        $result = $builder->lt('price', 10)->toArray();
        $expected = [
            'price' => ['$lt' => 10]
        ];
        $this->assertEquals($expected, $result);

        $result = $builder->lt('year', '2014')->toArray();
        $expected = [
            'year' => ['$lt' => 2014]
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * Tests the lte() filter
     *
     * @return void
     */
    public function testLte()
    {
        $builder = new FilterBuilder();
        $result = $builder->lte('price', 10)->toArray();
        $expected = [
            'price' => ['$lte' => 10]
        ];
        $this->assertEquals($expected, $result);

        $result = $builder->lte('year', '2014')->toArray();
        $expected = [
            'year' => ['$lte' => 2014]
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * Tests the ne() filter
     *
     * @return void
     */
    public function testNe()
    {
        $builder = new FilterBuilder();
        $result = $builder->ne('price', 10)->toArray();
        $expected = [
            'price' => ['$ne' => 10]
        ];
        $this->assertEquals($expected, $result);

        $result = $builder->ne('year', '2014')->toArray();
        $expected = [
            'year' => ['$ne' => 2014]
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * Tests the in() filter
     *
     * @return void
     */
    public function testIn()
    {
        $builder = new FilterBuilder();
        $result = $builder->in('price', 10)->toArray();
        $expected = [
            'price' => ['$in' => [10]]
        ];
        $this->assertEquals($expected, $result);

        $result = $builder->in('year', [2014, 2015])->toArray();
        $expected = [
            'year' => ['$in' => [2014, 2015]]
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * Tests the nin() filter
     *
     * @return void
     */
    public function testNin()
    {
        $builder = new FilterBuilder();
        $result = $builder->nin('price', 10)->toArray();
        $expected = [
            'price' => ['$nin' => [10]]
        ];
        $this->assertEquals($expected, $result);

        $result = $builder->nin('year', [2014, 2015])->toArray();
        $expected = [
            'year' => ['$nin' => [2014, 2015]]
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * Tests the or() method
     *
     * @return void
     */
    public function testOr()
    {
        $builder = new FilterBuilder;
        $result = $builder->or(
            $builder->lt('quantity', 20),
            $builder->eq('price', 10)
        )->toArray();

        $expected = [
            '$or' => [
                ['quantity' => ['$lt' => 20]],
                ['price' => 10]
            ]
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * Tests the and() method
     *
     * @return void
     */
    public function testAnd()
    {
        $builder = new FilterBuilder;
        $result = $builder->and(
            $builder->lt('quantity', 20),
            $builder->eq('price', 10)
        )->toArray();

        $expected = [
            '$and' => [
                ['quantity' => ['$lt' => 20]],
                ['price' => 10]
            ]
        ];
        $this->assertEquals($expected, $result);
    }

    /**
     * Tests the not() filter
     *
     * @return void
     */
    public function testNot()
    {
        $builder = new FilterBuilder;

        $result = $builder->not(
            $builder->in('title', ['cake', 'orm'])
        )->toArray();

        $expected = [
            'title' => [
                '$not' => ['$in' => ['cake', 'orm']]
            ]
        ];

        $this->assertEquals($expected, $result);
    }

    /**
     * Tests the nor() filter
     *
     * @return void
     */
    public function testNor()
    {
        $builder = new FilterBuilder;

        $result = $builder->nor(
            $builder->eq('title', 1),
            $builder->lt('quantity', 20)
        )->toArray();

        $expected = [
            '$nor' => [
                ['title' => 1],
                ['quantity' => ['$lt' => 20]]
            ]
        ];

        $this->assertEquals($expected, $result);
    }

    /**
     * Tests the exists() filter
     *
     * @return void
     */
    public function testExists()
    {
        $builder = new FilterBuilder;

        $result = $builder->exists('title')->toArray();

        $expected = ['title' => ['$exists' => true]];

        $this->assertEquals($expected, $result);
    }


    /**
     * Tests the parse() method
     *
     * @return void
     */
    public function testParseSingleArray()
    {
        $this->markTestIncomplete();
        $builder = new FilterBuilder;
        $filter = $builder->parse([
            'name' => 'jose',
            'age >=' => 29,
            'age <=' => 50,
            'salary >' => 50,
            'salary <' => 60,
            'interests in' => ['cakephp', 'food'],
            'interests not in' => ['boring stuff', 'c#'],

            'profile is' => null,
            'tags is not' => null,
            'address is' => 'something',
            'address is not' => 'something else',
            'last_name !=' => 'gonzalez',
        ]);

//        $builder->term('name', 'jose'),
//        $builder->gte('age', 29),
//        $builder->lte('age', 50),
//        $builder->gt('salary', 50),
//        $builder->lt('salary', 60),
//        $builder->terms('interests', ['cakephp', 'food']),
//        $builder->not($builder->terms('interests', ['boring stuff', 'c#'])),
//
//        $builder->missing('profile'),
//        $builder->exists('tags'),
//        $builder->term('address', 'something'),
//        $builder->not($builder->term('address', 'something else')),
//        $builder->not($builder->term('last_name', 'gonzalez'))

        $expected = [
            $builder->eq('name', 'jose'),
            $builder->gte('age', 29),
            $builder->lte('age', 50),
            $builder->gt('salary', 50),
            $builder->lt('salary', 60),
            $builder->in('interests', ['cakephp', 'food']),
            $builder->nin('interests', ['boring stuff', 'c#']),
        ];
        $this->assertEquals($expected, $filter);
    }

    /**
     * Tests the parse() method for generating or conditions
     *
     * @return void
     */
    public function testParseOr()
    {
        $this->markTestIncomplete();

        $builder = new FilterBuilder;
        $filter = $builder->parse([
            'or' => [
                'name' => 'jose',
                'age >' => 29
            ]
        ]);
        $expected = [
            $builder->or(
                $builder->term('name', 'jose'),
                $builder->gt('age', 29)
            )
        ];
        $this->assertEquals($expected, $filter);
    }

    /**
     * Tests the parse() method for generating and conditions
     *
     * @return void
     */
    public function testParseAnd()
    {
        $this->markTestIncomplete();

        $builder = new FilterBuilder;
        $filter = $builder->parse([
            'and' => [
                'name' => 'jose',
                'age >' => 29
            ]
        ]);
        $expected = [
            $builder->and(
                $builder->term('name', 'jose'),
                $builder->gt('age', 29)
            )
        ];
        $this->assertEquals($expected, $filter);
    }

    /**
     * Tests the parse() method for generating not conditions
     *
     * @return void
     */
    public function testParseNot()
    {
        $this->markTestIncomplete();

        $builder = new FilterBuilder;
        $filter = $builder->parse([
            'not' => [
                'name' => 'jose',
                'age >' => 29
            ]
        ]);
        $expected = [
            $builder->not(
                $builder->and(
                    $builder->term('name', 'jose'),
                    $builder->gt('age', 29)
                )
            )
        ];
        $this->assertEquals($expected, $filter);
    }
}
