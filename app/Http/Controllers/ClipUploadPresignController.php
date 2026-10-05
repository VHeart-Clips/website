<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Filament\Forms\Components\CustomFileUpload;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Routing\Attributes\Controllers\Middleware;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Number;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use SensitiveParameter;

#[Middleware('throttle:15,2')]
class ClipUploadPresignController
{
    public function __invoke(Request $request): array
    {
        $config = $this->parseUploadToken($request->string('token')->toString());
        $maxBytes = $config['maxSize'] ? $config['maxSize'] * 1024 : null;

        $data = $request->validate([
            'mime' => ['required', 'string', Rule::in(array_keys($config['acceptedFileTypes']))],
            'size' => ['required', 'integer', 'min:1', Rule::when($maxBytes, 'max:'.$maxBytes)],
            'previous' => ['nullable', 'string'],
        ], $maxBytes ? ['size.max' => 'The file must not be greater than '.Number::fileSize($maxBytes).'.'] : []);

        $userId = $request->user()?->getAuthIdentifier();
        $disk = Storage::disk($config['disk']);

        if (CustomFileUpload::isTemporaryPrefixOf($data['previous'] ?? null, $userId)) {
            $disk->delete($data['previous']);
        }

        $key = CustomFileUpload::temporaryPrefixFor($userId).Str::uuid().'.'.$config['acceptedFileTypes'][$data['mime']];

        Log::debug('Creating temporary Upload Url', [
            'key' => $key,
            'mime' => $data['mime'],
            'size' => $data['size'],
            'config' => $config,
            'user_id' => $userId,
        ]);

        $signed = $disk->temporaryUploadUrl($key, now()->addMinutes(30), [
            'ContentType' => $data['mime'],
            'ContentLength' => $data['size'],
        ]);

        return [
            'key' => $key,
            'url' => $signed['url'],
            'headers' => collect($signed['headers'])
                ->except(['Host', 'Content-Length'])
                ->map(fn ($v): string => implode(',', (array) $v)),
        ];
    }

    private function parseUploadToken(#[SensitiveParameter] string $token): array
    {
        try {
            $data = Crypt::decrypt($token);
        } catch (DecryptException) {
            abort(422, 'Invalid or tampered upload token.');
        }

        if (
            ! is_array($data)
            || ! isset($data['expires_at'])
            || now()->timestamp > $data['expires_at']
        ) {
            abort(422, 'Upload token has expired.');
        }

        if (! isset($data['user_id']) || $data['user_id'] !== auth()->id()) {
            abort(403, 'Upload token does not belong to you.');
        }

        $validator = Validator::make($data, [
            'user_id' => 'required|integer',
            'maxSize' => 'nullable|integer|min:1',
            'acceptedFileTypes' => 'required|array',
            'acceptedFileTypes.*' => 'string|alpha_dash',
            'disk' => 'required|string',
        ]);

        if ($validator->fails()) {
            Log::debug('Upload token parameters Invalid', ['token' => $data, 'errors' => $validator->errors()->getMessages()]);
            abort(422, 'Malformed upload token parameters.');
        }

        return $validator->validated();
    }
}
