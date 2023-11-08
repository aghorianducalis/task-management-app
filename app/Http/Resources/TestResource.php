<?php

declare(strict_types=1);

namespace App\Http\Resources;

use App\Models\Test;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property Test $resource
 */
class TestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->resource->id,
            'firstname'  => $this->resource->firstname,
            'middlename' => $this->resource->middlename,
            'lastname'   => $this->resource->lastname,
            'location'   => $this->resource->location,
            'rate'       => $this->resource->rate,
            'criteria'   => $this->resource->criteria,
            'manager_id' => $this->resource->manager_id,
            'created_at' => $this->resource->created_at,
            'updated_at' => $this->resource->updated_at,
        ];
    }
}
