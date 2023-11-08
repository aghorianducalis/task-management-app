<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Enums\RoleEnum;
use App\Http\Requests\IndexUserRequest;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserCollection;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @param \App\Http\Requests\IndexUserRequest $request
     * @param \App\Services\UserService $service
     * @return \App\Http\Resources\UserCollection
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(IndexUserRequest $request, UserService $service): UserCollection
    {
        $this->authorize('viewAny', User::class);

        $users = $service->getAllUsers();

        return new UserCollection($users);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\StoreUserRequest $request
     * @param \App\Services\UserService $service
     * @return \App\Http\Resources\UserResource
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(StoreUserRequest $request, UserService $service): UserResource
    {
        $this->authorize('create', User::class);

        $data = array_merge($request->validated(), ['role' => RoleEnum::Manager]);
        $user = $service->createUser($data);

        return new UserResource($user);
    }

    /**
     * Display the specified resource.
     *
     * @param int $userId
     * @param \App\Services\UserService $service
     * @return \App\Http\Resources\UserResource
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function show(int $userId, UserService $service): UserResource
    {
        $this->authorize('view', [User::class, $userId]);

        $user = $service->getUserById($userId);

        return new UserResource($user);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \App\Http\Requests\UpdateUserRequest $request
     * @param int $userId
     * @param \App\Services\UserService $service
     * @return \App\Http\Resources\UserResource
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(UpdateUserRequest $request, int $userId, UserService $service): UserResource
    {
        $this->authorize('update', [User::class, $userId]);

        $user = $service->updateUser($request->validated(), $userId);

        return new UserResource($user);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $userId
     * @param \App\Services\UserService $service
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function destroy(int $userId, UserService $service): JsonResponse
    {
        $this->authorize('delete', [User::class, $userId]);

        $result = $service->deleteUser($userId);

        return response()->json(['result' => $result]);
    }
}
