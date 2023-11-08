<?php

namespace App\Http\Requests;

use App\Enums\RoleEnum;
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
class StoreTestRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
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
                'required',
                'int',
                'min:0',
                'exists:users,id',
                new UserOfRole(RoleEnum::Manager),
            ],
        ];
    }
}
