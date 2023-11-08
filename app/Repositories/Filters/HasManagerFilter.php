<?php

declare(strict_types=1);

namespace App\Repositories\Filters;

use Illuminate\Database\Eloquent\Builder;

class HasManagerFilter implements FilterInterface
{
    protected ?int $managerId;

    public function __construct(?int $userId)
    {
        $this->managerId = $userId;
    }

    public function filter(Builder $query): Builder
    {
        if ($this->managerId) {
            $query->whereHas('manager', function (Builder $userQuery) {
                $userQuery->where('manager_id', $this->managerId);
            });
        }

        return $query;
    }
}
