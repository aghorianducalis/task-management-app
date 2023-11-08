<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Test;
use App\Repositories\Interfaces\TestRepositoryInterface;
use Illuminate\Support\Collection;

class TestService
{
    protected TestRepositoryInterface $repository;

    public function __construct(TestRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function getAllTests(): Collection
    {
        return $this->repository->matching();
    }

    public function getTestById(int $testId): Test
    {
        return $this->repository->find($testId);
    }

    public function getTestsByManager(?int $managerId): Collection
    {
        return $this->repository->findByManager($managerId);
    }

    public function isTestBelongsToManager(int $testId, int $userId): bool
    {
        return $this->getTestsByManager($userId)->pluck('id')->contains($testId);
    }

    public function createTest(array $data): Test
    {
        $data['criteria'] = $this->calculateCriteria($data['rate']);

        return $this->repository->create($data);
    }

    public function updateTest(array $data, int $testId): Test
    {
        $data['criteria'] = $this->calculateCriteria($data['rate']);

        return $this->repository->update($data, $testId);
    }

    public function deleteTest(int $testId): bool
    {
        return $this->repository->delete($testId);
    }

    public function calculateCriteria(int $rate): int
    {
        return match (true) {
            $rate >= 100 => 500,
            $rate >= 91  => 300,
            $rate >= 80  => 200,
            $rate >= 60  => 100,
            default      => 0,
        };
    }

    public static function getInstance(): TestService
    {
        return app(TestService::class);
    }
}
