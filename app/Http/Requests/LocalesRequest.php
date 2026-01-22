<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class LocalesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'locale' => ['required', 'string', 'regex:/^[\w\-\s]+$/'],
            'namespace' => ['required', 'string', 'regex:/^[\w\-\s]+$/'],
        ];
    }

    /**
     * Get the sorted and parsed locales.
     *
     * @return string[]
     */
    public function locales(): array
    {
        return $this->getSortedList('locale');
    }

    /**
     * Get the sorted and parsed namespaces.
     *
     * @return string[]
     */
    public function namespaces(): array
    {
        return $this->getSortedList('namespace');
    }

    /**
     * Helper to explode, handle nulls, and sort the input.
     *
     * @return string[]
     */
    protected function getSortedList(string $key): array
    {
        $input = $this->string($key, '');

        $items = explode(' ', (string) $input);

        $items = array_filter($items);
        sort($items);

        return $items;
    }

    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'errors' => $validator->errors(),
        ], 422));
    }
}
