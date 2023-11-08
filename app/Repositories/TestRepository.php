<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Models\Test;
use App\Repositories\Filters\FilterCriteria;
use App\Repositories\Filters\HasManagerFilter;
use App\Repositories\Interfaces\TestRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class TestRepository extends EloquentRepository implements TestRepositoryInterface
{
    public function findByManager(?int $managerId): Collection
    {
        $criteria = new FilterCriteria;
        $criteria->push(new HasManagerFilter($managerId));

        return $this->matching($criteria);
    }

    public function attachManager(Test $test, int $managerId): void
    {
        $test->manager()->associate($managerId);
    }

    protected function query(): Builder
    {
        return Test::query();
    }
}
