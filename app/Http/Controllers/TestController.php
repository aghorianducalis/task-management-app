<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\RoleEnum;
use App\Http\Requests\IndexTestRequest;
use App\Http\Requests\StoreTestRequest;
use App\Http\Requests\UpdateTestRequest;
use App\Http\Resources\TestCollection;
use App\Http\Resources\TestResource;
use App\Models\Test;
use App\Models\User;
use App\Services\TestService;
use Illuminate\Http\JsonResponse;

class TestController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \App\Http\Requests\IndexTestRequest $request
     * @param \App\Services\TestService $service
     * @return \App\Http\Resources\TestCollection
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(IndexTestRequest $request, TestService $service): TestCollection
    {
        $this->authorize('viewAny', Test::class);
        /** @var User $user */
        $user = auth()->user();

        $tests = $user?->hasRole(RoleEnum::Admin->value) ?
            $service->getAllTests() :
            $service->getTestsByManager($user?->id);

        return new TestCollection($tests);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\StoreTestRequest $request
     * @param \App\Services\TestService $service
     * @return \App\Http\Resources\TestResource
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(StoreTestRequest $request, TestService $service): TestResource
    {
        $this->authorize('create', Test::class);

        $data = array_merge($request->validated(), ['role' => RoleEnum::Manager]);
        $test = $service->createTest($data);

        return new TestResource($test);
    }

    /**
     * Display the specified resource.
     *
     * @param int $testId
     * @param \App\Services\TestService $service
     * @return \App\Http\Resources\TestResource
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(int $testId, TestService $service): TestResource
    {
        $this->authorize('view', [Test::class, $testId]);

        $test = $service->getTestById($testId);

        return new TestResource($test);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\UpdateTestRequest $request
     * @param int $testId
     * @param \App\Services\TestService $service
     * @return \App\Http\Resources\TestResource
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(UpdateTestRequest $request, int $testId, TestService $service): TestResource
    {
        $this->authorize('update', [Test::class, $testId]);

        /** @var User $user */
        $user = auth()->user();

        $updateData = $user->hasRole(RoleEnum::Manager->value) ?
            $request->only(['rate']) :
            $request->validated();

        $test = $service->updateTest($updateData, $testId);

        return new TestResource($test);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $testId
     * @param \App\Services\TestService $service
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(int $testId, TestService $service): JsonResponse
    {
        $this->authorize('delete', [Test::class, $testId]);

        $result = $service->deleteTest($testId);

        return response()->json(['result' => $result]);
    }
}
