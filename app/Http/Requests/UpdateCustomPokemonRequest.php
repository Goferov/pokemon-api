<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCustomPokemonRequest extends FormRequest
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
            'types'       => ['nullable', 'array'],
            'types.*'     => ['string', 'max:255'],
            'height'      => ['nullable', 'integer', 'min:1'],
            'weight'      => ['nullable', 'integer', 'min:1'],
            'stats'       => ['nullable', 'array'],
            'description' => ['nullable', 'string'],
        ];
    }
}
