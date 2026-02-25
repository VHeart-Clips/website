<?php

declare(strict_types=1);

namespace App\Http\Requests;

use App\Enums\ExternalContentProxyType;
use App\Models\Contracts\ExternalProxyable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class ContentProxyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            /** @var ExternalContentProxyType|null $type */
            $type = $this->route('type');

            if (! $type instanceof ExternalContentProxyType) {
                return;
            }

            $extension = $this->route('extension');

            /** @var class-string<Model&ExternalProxyable> $modelClass */
            $modelClass = $type->modelClass();

            if ($extension !== $modelClass::getProxyExtension()) {
                abort(404);
            }
        });
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'identifier' => $this->route('identifier'),
            'extension' => $this->route('extension'),
        ]);
    }
}
