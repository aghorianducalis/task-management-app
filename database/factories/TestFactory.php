<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\RoleEnum;
use App\Models\Test;
use App\Models\User;
use App\Services\TestService;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Test>
 */
class TestFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = Test::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'firstname'  => fake()->firstName(),
            'middlename' => fake()->firstName(),
            'lastname'   => fake()->lastName(),
            'location'   => fake()->address(),
            'rate'       => fake()->numberBetween(0, 100),
            'criteria'   => function (array $attributes) {
                return TestService::getInstance()->calculateCriteria($attributes['rate']);
            },
            'manager_id' => User::factory()->withRole(RoleEnum::Manager),
        ];
    }
}
