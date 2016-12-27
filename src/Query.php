<?php


namespace Dilab\CakeMongo;

use Cake\Datasource\QueryTrait;
use IteratorAggregate;

class Query implements IteratorAggregate
{
    use QueryTrait;

    /**
     * Query constructor.
     * @param \Dilab\CakeMongo\Collection $repository
     */
    public function __construct(Collection $repository)
    {
        $this->repository($repository);
    }

    public function applyOptions(array $options)
    {
        // TODO: Implement applyOptions() method.
    }

    protected function _execute()
    {
        // TODO: Implement _execute() method.
    }


}