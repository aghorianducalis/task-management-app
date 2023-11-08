<?php

declare(strict_types=1);

namespace App\Repositories\Interfaces;

use App\Repositories\Filters\FilterCriteria;
use Illuminate\Support\Collection;

interface RepositoryInterface
{
    public function matching(FilterCriteria $criteria = null): Collection;

    public function find($id);

    public function create(array $data);

    public function update(array $data, $id);

    public function delete($id): bool;
}
