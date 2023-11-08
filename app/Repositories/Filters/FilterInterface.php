<?php

declare(strict_types=1);

namespace App\Repositories\Filters;

use Illuminate\Database\Eloquent\Builder;

interface FilterInterface
{
    public function filter(Builder $query): Builder;
}
