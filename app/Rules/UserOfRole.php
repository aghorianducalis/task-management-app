<?php

namespace App\Rules;

use App\Enums\RoleEnum;
use App\Services\UserService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UserOfRole implements ValidationRule
{
    private RoleEnum $roleEnum;

    public function __construct(RoleEnum $roleEnum)
    {
        $this->roleEnum = $roleEnum;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $user = UserService::getInstance()->getUserById($value);

        if (!$user || !$user->hasRole($this->roleEnum->value)) {
            $fail("User with id = {$value} is now valid {$this->roleEnum->value}");
        }
    }
}
