<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\RoleEnum;
use App\Models\User;
use App\Rules\UserOfRole;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @property string $firstname
 * @property string $middlename
 * @property string $lastname
 * @property string $location
 * @property int $rate
 * @property int $manager_id
 */
class UpdateTestRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() && ($this->user() instanceof User);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var User $user */
        $user = $this->user();

        return $user->hasRole(RoleEnum::Manager->value) ?
            [
                'rate' => [
                    'required',
                    'int',
                    'min:0',
                    'max:100',
                ],
            ] :
            [
                'firstname' => [
                    'required',
                    'string',
                    'max:255',
                ],
                'middlename' => [
                    'required',
                    'string',
                    'max:255',
                ],
                'lastname' => [
                    'required',
                    'string',
                    'max:255',
                ],
                'location' => [
                    'required',
                    'string',
                    'max:255',
                ],
                'rate' => [
                    'required',
                    'int',
                    'min:0',
                    'max:100',
                ],
                'manager_id' => [
                    'bail',
                    'required',
                    'int',
                    'min:0',
                    'exists:users,id',
                    new UserOfRole(RoleEnum::Manager),
                ],
            ];
    }
}
